<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RideOffer extends Model
{
    public const RESPONSE_PENDING = 'pending';
    public const RESPONSE_ACCEPTED = 'accepted';
    public const RESPONSE_REJECTED = 'rejected';
    public const RESPONSE_IGNORED = 'ignored';
    public const RESPONSE_CANCELLED_AFTER_ACCEPT = 'cancelled_after_accept';
    public const RESPONSE_CANCELLED_BY_USER = 'cancelled_by_user';

    protected $table = 'ride_offers';

    protected $fillable = [
        'ride_id',
        'driver_id',
        'offered_at',
        'responded_at',
        'response',
        'was_accepted',
        'cancelled_at',
        'response_seconds',
        'attempt',
        'note',
        'driver_lat',
        'driver_lng',
    ];

    protected $casts = [
        'offered_at' => 'datetime',
        'responded_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'was_accepted' => 'boolean',
        'attempt' => 'integer',
        'response_seconds' => 'integer',
        'driver_lat' => 'float',
        'driver_lng' => 'float',
    ];

    public function hasDriverLocation(): bool
    {
        return $this->driver_lat !== null && $this->driver_lng !== null;
    }

    public function ride()
    {
        return $this->belongsTo(Ride::class);
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * Human-readable Arabic/English label + bootstrap color for the response.
     * Used by the admin dashboard offer history table.
     */
    public static function responseLabels(): array
    {
        return [
            self::RESPONSE_PENDING => ['label' => __('Pending'), 'color' => 'warning'],
            self::RESPONSE_ACCEPTED => ['label' => __('Accepted'), 'color' => 'success'],
            self::RESPONSE_REJECTED => ['label' => __('Rejected'), 'color' => 'danger'],
            self::RESPONSE_IGNORED => ['label' => __('Ignored'), 'color' => 'secondary'],
            self::RESPONSE_CANCELLED_AFTER_ACCEPT => ['label' => __('Cancelled after accept'), 'color' => 'danger'],
            self::RESPONSE_CANCELLED_BY_USER => ['label' => __('Cancelled by user'), 'color' => 'dark'],
        ];
    }

    public function responseLabel(): string
    {
        return self::responseLabels()[$this->response]['label'] ?? $this->response;
    }

    public function responseColor(): string
    {
        return self::responseLabels()[$this->response]['color'] ?? 'secondary';
    }

    /**
     * Accept followed by a cancel stays on one row: both outcomes are shown
     * so the admin does not read a second captain as the one who cancelled.
     *
     * @return array<int, array{label: string, color: string}>
     */
    public function responseBadges(): array
    {
        $final = self::responseLabels()[$this->response] ?? ['label' => $this->response, 'color' => 'secondary'];

        if ($this->was_accepted && $this->response !== self::RESPONSE_ACCEPTED) {
            $accepted = self::responseLabels()[self::RESPONSE_ACCEPTED];

            return [$accepted, $final];
        }

        return [$final];
    }

    /**
     * The reason text for a cancel that happened after this captain accepted.
     * Older rows stored "reason_id:12"; those are resolved to the reason name.
     */
    public function cancellationReasonLabel(): ?string
    {
        if (!$this->was_accepted || !in_array($this->response, [
            self::RESPONSE_CANCELLED_AFTER_ACCEPT,
            self::RESPONSE_CANCELLED_BY_USER,
        ], true)) {
            return null;
        }

        $note = trim((string) $this->note);
        if ($note === '' || in_array($note, [
            'passenger_cancelled',
            'auto_timeout',
            'auto_timeout_command',
            'cycling',
            'reassignment',
            'initial_assignment',
            'reassignment_command',
            'cycling_command',
        ], true)) {
            return null;
        }

        if (preg_match('/^reason_id:(\d+)$/', $note, $matches)) {
            return CancellationReason::find($matches[1])?->reason;
        }

        return $note;
    }

    public function responseBadgeHtml(): string
    {
        return collect($this->responseBadges())
            ->map(function (array $badge) {
                return sprintf(
                    '<span class="badge bg-%s me-1">%s</span>',
                    e($badge['color']),
                    e($badge['label'])
                );
            })
            ->implode('');
    }
}
