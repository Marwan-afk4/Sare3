@extends('layouts.app')
@php $currentPage = 'leaderboard'; @endphp
@section('title', __('Leaderboard & Bonuses'))

@section('content')
<div class="container-fluid">

    {{-- ── Page Header ── --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0 fw-bold">
                <span data-feather="award" class="me-2"></span>{{ __('Driver Leaderboard & Bonuses') }}
            </h2>
            <p class="text-muted mb-0 mt-1">{{ __('Track driver performance, manage bonus tiers, and grant rewards.') }}</p>
        </div>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#grantBonusModal">
            <span data-feather="gift" class="me-1"></span>{{ __('Grant Bonus') }}
        </button>
    </div>

    {{-- ── Flash Messages ── --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ── Summary Cards ── --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg,#4e73df,#224abe);">
                <div class="card-body text-white d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-white-50 small mb-1">{{ __('Drivers on Leaderboard') }}</div>
                        <div class="fs-3 fw-bold">{{ $totalDriversRanked }}</div>
                    </div>
                    <span data-feather="users" style="width:40px;height:40px;opacity:.6;"></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg,#1cc88a,#13855c);">
                <div class="card-body text-white d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-white-50 small mb-1">{{ __('Total Bonuses Granted') }}</div>
                        <div class="fs-3 fw-bold">{{ number_format($totalBonusGranted, 2) }}</div>
                    </div>
                    <span data-feather="dollar-sign" style="width:40px;height:40px;opacity:.6;"></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg,#f6c23e,#c09000);">
                <div class="card-body text-white d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-white-50 small mb-1">{{ __('Tier Bonuses') }}</div>
                        <div class="fs-3 fw-bold">{{ number_format($tierBonusGranted, 2) }}</div>
                    </div>
                    <span data-feather="award" style="width:40px;height:40px;opacity:.6;"></span>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg,#e74a3b,#be2617);">
                <div class="card-body text-white d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-white-50 small mb-1">{{ __('Manual Bonuses') }}</div>
                        <div class="fs-3 fw-bold">{{ number_format($manualBonusGranted, 2) }}</div>
                    </div>
                    <span data-feather="gift" style="width:40px;height:40px;opacity:.6;"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Tabs ── --}}
    <ul class="nav nav-tabs mb-4" id="leaderboardTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" id="tab-leaderboard" data-bs-toggle="tab" data-bs-target="#pane-leaderboard" type="button">
                <span data-feather="bar-chart-2" class="me-1"></span>{{ __('Leaderboard') }}
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="tab-tiers" data-bs-toggle="tab" data-bs-target="#pane-tiers" type="button">
                <span data-feather="layers" class="me-1"></span>{{ __('Bonus Tiers') }}
                <span class="badge bg-secondary ms-1">{{ $tiers->count() }}</span>
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link" id="tab-history" data-bs-toggle="tab" data-bs-target="#pane-history" type="button">
                <span data-feather="clock" class="me-1"></span>{{ __('Grant History') }}
            </button>
        </li>
    </ul>

    <div class="tab-content">

        {{-- ══════════ TAB 1: LEADERBOARD ══════════ --}}
        <div class="tab-pane fade show active" id="pane-leaderboard">

            {{-- Period Filter --}}
            <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                <span class="text-muted fw-semibold me-2">{{ __('Period:') }}</span>
                @foreach(['weekly' => __('This Week'), 'monthly' => __('This Month'), 'all_time' => __('All Time')] as $value => $label)
                    <a href="{{ route('leaderboard.index', ['period_type' => $value]) }}"
                       class="btn btn-sm {{ $periodType === $value ? 'btn-primary' : 'btn-outline-primary' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3" style="width:60px">{{ __('Rank') }}</th>
                                    <th>{{ __('Driver') }}</th>
                                    <th class="text-center">{{ __('Completed Rides') }}</th>
                                    <th class="text-center">{{ __('Total Earned') }}</th>
                                    <th class="text-center">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leaderboard as $entry)
                                    <tr>
                                        <td class="ps-3">
                                            @if($entry['rank'] === 1)
                                                <span class="fs-4" title="1st">🥇</span>
                                            @elseif($entry['rank'] === 2)
                                                <span class="fs-4" title="2nd">🥈</span>
                                            @elseif($entry['rank'] === 3)
                                                <span class="fs-4" title="3rd">🥉</span>
                                            @else
                                                <span class="text-muted fw-bold">#{{ $entry['rank'] }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                @if($entry['image'])
                                                    <img src="{{ $entry['image'] }}" class="rounded-circle"
                                                         style="width:38px;height:38px;object-fit:cover;" alt="">
                                                @else
                                                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center text-white fw-bold"
                                                         style="width:38px;height:38px;font-size:14px;">
                                                        {{ strtoupper(substr($entry['name'] ?? '?', 0, 1)) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="fw-semibold">{{ $entry['name'] ?? __('Unknown') }}</div>
                                                    <div class="text-muted small">ID #{{ $entry['driver_id'] }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary fs-6 px-3">{{ $entry['rides_count'] }}</span>
                                        </td>
                                        <td class="text-center fw-semibold text-success">
                                            {{ number_format($entry['total_earned'], 2) }}
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-success"
                                                    onclick="openGrantModal({{ $entry['driver_id'] }}, '{{ addslashes($entry['name']) }}')"
                                                    title="{{ __('Grant Bonus') }}">
                                                <span data-feather="gift" style="width:14px;height:14px;"></span>
                                                {{ __('Grant Bonus') }}
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <span data-feather="inbox" style="width:48px;height:48px;opacity:.3;"></span>
                                            <p class="mt-2 mb-0">{{ __('No drivers found for this period.') }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════ TAB 2: BONUS TIERS ══════════ --}}
        <div class="tab-pane fade" id="pane-tiers">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="text-muted mb-0">{{ __('Define milestone thresholds. When a driver reaches a rides target in a period, they automatically receive the bonus in their wallet.') }}</p>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createTierModal">
                    <span data-feather="plus" class="me-1"></span>{{ __('Add Tier') }}
                </button>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">{{ __('Name') }}</th>
                                    <th class="text-center">{{ __('Rides Required') }}</th>
                                    <th class="text-center">{{ __('Bonus Amount') }}</th>
                                    <th class="text-center">{{ __('Period') }}</th>
                                    <th class="text-center">{{ __('Status') }}</th>
                                    <th class="text-center">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tiers as $tier)
                                    <tr>
                                        <td class="ps-3 fw-semibold">
                                            {{ $tier->name ?: ('Tier #' . $tier->id) }}
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info text-dark fs-6 px-3">{{ $tier->rides_count }}</span>
                                        </td>
                                        <td class="text-center fw-bold text-success">
                                            {{ number_format($tier->bonus_amount, 2) }}
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $periodLabels = ['weekly' => 'Weekly', 'monthly' => 'Monthly', 'all_time' => 'All Time'];
                                                $periodColors = ['weekly' => 'bg-warning text-dark', 'monthly' => 'bg-primary', 'all_time' => 'bg-dark'];
                                            @endphp
                                            <span class="badge {{ $periodColors[$tier->period_type] ?? 'bg-secondary' }}">
                                                {{ $periodLabels[$tier->period_type] ?? $tier->period_type }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <form method="POST" action="{{ route('leaderboard.tier.toggle', $tier) }}" class="d-inline">
                                                @csrf @method('PATCH')
                                                <button type="submit"
                                                    class="badge border-0 {{ $tier->is_active ? 'bg-success' : 'bg-secondary' }} fs-6 px-3"
                                                    title="{{ $tier->is_active ? __('Click to deactivate') : __('Click to activate') }}">
                                                    {{ $tier->is_active ? __('Active') : __('Inactive') }}
                                                </button>
                                            </form>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-primary me-1"
                                                    onclick="openEditTierModal({{ $tier->id }}, '{{ addslashes($tier->name ?? '') }}', {{ $tier->rides_count }}, {{ $tier->bonus_amount }}, '{{ $tier->period_type }}')"
                                                    title="{{ __('Edit') }}">
                                                <span data-feather="edit-2" style="width:14px;height:14px;"></span>
                                            </button>
                                            <form method="POST" action="{{ route('leaderboard.tier.destroy', $tier) }}" class="d-inline"
                                                  onsubmit="return confirm('{{ __('Delete this tier?') }}')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="{{ __('Delete') }}">
                                                    <span data-feather="trash-2" style="width:14px;height:14px;"></span>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <span data-feather="layers" style="width:48px;height:48px;opacity:.3;"></span>
                                            <p class="mt-2 mb-0">{{ __('No bonus tiers configured yet.') }}</p>
                                            <button class="btn btn-primary btn-sm mt-3" data-bs-toggle="modal" data-bs-target="#createTierModal">
                                                {{ __('Add First Tier') }}
                                            </button>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════ TAB 3: GRANT HISTORY ══════════ --}}
        <div class="tab-pane fade" id="pane-history">

            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">{{ __('Driver') }}</th>
                                    <th class="text-center">{{ __('Amount') }}</th>
                                    <th class="text-center">{{ __('Type') }}</th>
                                    <th>{{ __('Note') }}</th>
                                    <th class="text-center">{{ __('Rides at Grant') }}</th>
                                    <th class="text-center">{{ __('Granted By') }}</th>
                                    <th class="text-center">{{ __('Date') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentGrants as $grant)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-semibold">{{ $grant->driver->name ?? __('Unknown') }}</div>
                                            <div class="text-muted small">ID #{{ $grant->driver_id }}</div>
                                        </td>
                                        <td class="text-center fw-bold text-success">
                                            +{{ number_format($grant->amount, 2) }}
                                        </td>
                                        <td class="text-center">
                                            @if($grant->type === 'manual')
                                                <span class="badge bg-danger">{{ __('Manual') }}</span>
                                            @else
                                                <span class="badge bg-info text-dark">
                                                    {{ __('Milestone') }}
                                                    @if($grant->tier)
                                                        ({{ $grant->tier->rides_count }} {{ __('rides') }})
                                                    @endif
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-muted small">{{ $grant->note ?? '—' }}</td>
                                        <td class="text-center">{{ $grant->rides_count }}</td>
                                        <td class="text-center text-muted small">
                                            {{ $grant->grantedByAdmin->name ?? __('System') }}
                                        </td>
                                        <td class="text-center text-muted small">
                                            {{ $grant->created_at->format('d M Y, H:i') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <span data-feather="clock" style="width:48px;height:48px;opacity:.3;"></span>
                                            <p class="mt-2 mb-0">{{ __('No bonuses have been granted yet.') }}</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($recentGrants->hasPages())
                        <div class="card-footer bg-white border-top-0">
                            {{ $recentGrants->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>{{-- end tab-content --}}
</div>


{{-- ══════════════════════════════════════════════════════════════════════
     MODALS
══════════════════════════════════════════════════════════════════════ --}}

{{-- ── Grant Bonus Modal ── --}}
<div class="modal fade" id="grantBonusModal" tabindex="-1" aria-labelledby="grantBonusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('leaderboard.grant') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="grantBonusModalLabel">
                        <span data-feather="gift" class="me-2"></span>{{ __('Grant Bonus to Driver') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ __('Select Driver') }}</label>
                        <select name="driver_id" id="grantDriverSelect" class="form-select" required>
                            <option value="">{{ __('— Choose a driver —') }}</option>
                            @foreach($drivers as $driver)
                                <option value="{{ $driver->id }}">
                                    {{ $driver->name }} ({{ $driver->phone }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">{{ __('Bonus Amount') }}</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <span data-feather="dollar-sign" style="width:14px;height:14px;"></span>
                            </span>
                            <input type="number" name="amount" class="form-control" min="0.01" step="0.01"
                                   placeholder="0.00" required>
                        </div>
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-semibold">{{ __('Note') }} <span class="text-muted fw-normal">({{ __('optional') }})</span></label>
                        <textarea name="note" class="form-control" rows="2"
                                  placeholder="{{ __('e.g. Performance bonus for March') }}"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-success">
                        <span data-feather="send" class="me-1"></span>{{ __('Grant & Add to Wallet') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ── Create Tier Modal ── --}}
<div class="modal fade" id="createTierModal" tabindex="-1" aria-labelledby="createTierLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('leaderboard.tier.store') }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createTierLabel">
                        <span data-feather="plus-circle" class="me-2"></span>{{ __('Add Bonus Tier') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('leaderboard._tier_fields')
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Create Tier') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- ── Edit Tier Modal ── --}}
<div class="modal fade" id="editTierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="editTierForm">
            @csrf @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <span data-feather="edit-2" class="me-2"></span>{{ __('Edit Bonus Tier') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    @include('leaderboard._tier_fields', ['editing' => true])
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openGrantModal(driverId, driverName) {
    const select = document.getElementById('grantDriverSelect');
    if (select) {
        select.value = driverId;
    }
    const modal = new bootstrap.Modal(document.getElementById('grantBonusModal'));
    modal.show();
}

function openEditTierModal(id, name, ridesCount, bonusAmount, periodType) {
    const form = document.getElementById('editTierForm');
    form.action = `/admin/leaderboard/tiers/${id}`;

    form.querySelector('[name="name"]').value = name;
    form.querySelector('[name="rides_count"]').value = ridesCount;
    form.querySelector('[name="bonus_amount"]').value = bonusAmount;
    form.querySelector('[name="period_type"]').value = periodType;

    const modal = new bootstrap.Modal(document.getElementById('editTierModal'));
    modal.show();
}

// Re-activate feather icons inside modals
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.modal').forEach(el => {
        el.addEventListener('shown.bs.modal', function () {
            if (typeof feather !== 'undefined') feather.replace();
        });
    });
});
</script>
@endpush
