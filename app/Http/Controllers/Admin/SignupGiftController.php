<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\User;
use App\Services\SignupGiftService;
use Illuminate\Http\Request;

class SignupGiftController extends Controller
{
    public function __construct(private SignupGiftService $signupGifts)
    {
    }

    public function index(Request $request)
    {
        $tab = $request->get('tab', 'pending');
        if (! in_array($tab, ['pending', 'granted'], true)) {
            $tab = 'pending';
        }

        $keyword = $request->get('keyword');
        $includeIncomplete = $request->boolean('incomplete');
        $giftAmount = $this->signupGifts->getAmount();
        $giftEnabled = $this->signupGifts->isEnabled();

        $pendingQuery = $this->signupGifts->pendingQuery(! $includeIncomplete);
        $grantedQuery = $this->signupGifts->grantedQuery();

        $stats = [
            'pending' => (clone $pendingQuery)->count(),
            'granted' => (clone $grantedQuery)->count(),
            'total_gifted' => (clone $grantedQuery)->sum('signup_gift_amount'),
        ];

        $usersQuery = $tab === 'granted' ? $grantedQuery : $pendingQuery;

        $usersQuery
            ->when($keyword, function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', "%{$keyword}%")
                        ->orWhere('email', 'LIKE', "%{$keyword}%")
                        ->orWhere('phone', 'LIKE', "%{$keyword}%");
                });
            })
            ->when($tab === 'granted', function ($query) {
                $query->with('signupGiftGrantedBy:id,name')
                    ->orderByDesc('signup_gift_received_at');
            }, function ($query) {
                $query->orderByDesc('created_at');
            });

        $users = $usersQuery->paginate(30)->withQueryString();

        return view('admin.signup-gifts.index', compact(
            'users',
            'tab',
            'keyword',
            'includeIncomplete',
            'stats',
            'giftAmount',
            'giftEnabled',
        ));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'signup_gift_enabled' => 'nullable|in:0,1,on',
            'signup_gift_amount' => 'required|numeric|min:0|max:999999',
        ]);

        AppSetting::setSignupGiftEnabled(
            in_array($request->input('signup_gift_enabled'), ['1', 'on', 1, true], true)
        );
        AppSetting::setSignupGiftAmount((float) $validated['signup_gift_amount']);

        return redirect()
            ->route('signup-gifts.index', $request->only('tab', 'keyword', 'incomplete'))
            ->with('success', __('Signup gift settings updated successfully.'));
    }

    public function grant(Request $request, User $user)
    {
        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:0.01|max:999999',
        ]);

        try {
            $result = $this->signupGifts->grantManually(
                $user,
                $request->user(),
                isset($validated['amount']) ? (float) $validated['amount'] : null
            );
        } catch (\InvalidArgumentException|\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with(
            'success',
            __('Gift of :amount added to the wallet of :name.', [
                'amount' => number_format($result['amount'], 2),
                'name' => $user->name ?: $user->phone ?: '#'.$user->id,
            ])
        );
    }

    public function grantBulk(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array|min:1|max:200',
            'user_ids.*' => 'integer|exists:users,id',
            'amount' => 'nullable|numeric|min:0.01|max:999999',
        ]);

        $users = User::whereIn('id', $validated['user_ids'])
            ->where('role', 'user')
            ->get();

        try {
            $stats = $this->signupGifts->grantBulk(
                $users,
                $request->user(),
                isset($validated['amount']) ? (float) $validated['amount'] : null
            );
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with(
            'success',
            __('Granted :granted gift(s). Skipped :skipped. Failed :failed.', [
                'granted' => $stats['granted'],
                'skipped' => $stats['skipped'],
                'failed' => $stats['failed'],
            ])
        );
    }
}
