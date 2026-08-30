@extends('layouts.app')
@php
    $currentPage = 'signup-gifts';
    $formattedAmount = number_format($giftAmount, 2);
@endphp
@section('title', __('Signup Gift'))
@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
            <div>
                <h2 class="mb-0 fw-bold">
                    <span data-feather="gift" class="me-2"></span>{{ __('Signup Gift') }}
                </h2>
                <p class="text-muted mb-0 mt-1">
                    {{ __('Give new passengers a wallet gift on first signup, and catch up with anyone who missed it.') }}
                </p>
            </div>
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fa fa-users me-1"></i>{{ __('All Users') }}
            </a>
        </div>

        {{-- Settings --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="mb-0">
                        <i class="fas fa-sliders-h me-2 text-primary"></i>{{ __('Gift settings') }}
                    </h5>
                    <small class="text-muted">{{ __('This amount is added to the passenger wallet once, on first signup.') }}</small>
                </div>
                @if($giftEnabled && $giftAmount > 0)
                    <span class="badge bg-success-subtle text-success px-3 py-2">
                        <i class="fas fa-check-circle me-1"></i>{{ __('Auto gift is on') }} · {{ $formattedAmount }}
                    </span>
                @elseif($giftEnabled)
                    <span class="badge bg-warning-subtle text-warning px-3 py-2">
                        <i class="fas fa-exclamation-triangle me-1"></i>{{ __('On, but amount is zero') }}
                    </span>
                @else
                    <span class="badge bg-secondary-subtle text-secondary px-3 py-2">
                        <i class="fas fa-pause-circle me-1"></i>{{ __('Auto gift is off') }}
                    </span>
                @endif
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('signup-gifts.settings') }}" class="row g-3 align-items-end">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <input type="hidden" name="keyword" value="{{ $keyword }}">
                    <input type="hidden" name="incomplete" value="{{ $includeIncomplete ? 1 : 0 }}">

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">{{ __('Automatic gift') }}</label>
                        <div class="form-check form-switch mt-1">
                            <input type="hidden" name="signup_gift_enabled" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" id="signup_gift_enabled"
                                name="signup_gift_enabled" value="1" {{ $giftEnabled ? 'checked' : '' }}>
                            <label class="form-check-label" for="signup_gift_enabled" id="signup_gift_enabled_label">
                                {{ $giftEnabled ? __('Enabled') : __('Disabled') }}
                            </label>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label for="signup_gift_amount" class="form-label fw-semibold">{{ __('Gift amount') }}</label>
                        <div class="input-group">
                            <input type="number" class="form-control form-control-lg" id="signup_gift_amount"
                                name="signup_gift_amount" value="{{ old('signup_gift_amount', $giftAmount) }}"
                                min="0" max="999999" step="0.01" required>
                            <span class="input-group-text">{{ __('JOD') }}</span>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-1"></i>{{ __('Save settings') }}
                        </button>
                    </div>
                </form>

                <div class="alert alert-info mt-4 mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    @if($giftEnabled && $giftAmount > 0)
                        {{ __('New passengers will receive :amount in their wallet when they finish signing up. Existing users stay in the pending list until you grant them.', ['amount' => $formattedAmount]) }}
                    @else
                        {{ __('Turn this on and set an amount so new signups receive the gift automatically. You can still grant it manually to anyone in the pending list.') }}
                    @endif
                </div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg,#f6c23e,#c09000);">
                    <div class="card-body text-white d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small mb-1">{{ __('Waiting for gift') }}</div>
                            <div class="fs-3 fw-bold">{{ number_format($stats['pending']) }}</div>
                        </div>
                        <span data-feather="clock" style="width:40px;height:40px;opacity:.6;"></span>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg,#1cc88a,#13855c);">
                    <div class="card-body text-white d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small mb-1">{{ __('Already gifted') }}</div>
                            <div class="fs-3 fw-bold">{{ number_format($stats['granted']) }}</div>
                        </div>
                        <span data-feather="check-circle" style="width:40px;height:40px;opacity:.6;"></span>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg,#4e73df,#224abe);">
                    <div class="card-body text-white d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small mb-1">{{ __('Total gifted') }}</div>
                            <div class="fs-3 fw-bold">{{ number_format($stats['total_gifted'], 2) }}</div>
                        </div>
                        <span data-feather="dollar-sign" style="width:40px;height:40px;opacity:.6;"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- List --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent">
                <ul class="nav nav-tabs card-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === 'pending' ? 'active' : '' }}"
                            href="{{ route('signup-gifts.index', ['tab' => 'pending', 'keyword' => $keyword, 'incomplete' => $includeIncomplete ? 1 : null]) }}">
                            <i class="fas fa-hourglass-half me-1"></i>{{ __('Did not get a gift') }}
                            <span class="badge bg-warning text-dark ms-1">{{ number_format($stats['pending']) }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === 'granted' ? 'active' : '' }}"
                            href="{{ route('signup-gifts.index', ['tab' => 'granted', 'keyword' => $keyword, 'incomplete' => $includeIncomplete ? 1 : null]) }}">
                            <i class="fas fa-gift me-1"></i>{{ __('Gifted users') }}
                            <span class="badge bg-success ms-1">{{ number_format($stats['granted']) }}</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <form method="GET" action="{{ route('signup-gifts.index') }}" class="d-flex gap-2 align-items-center flex-wrap">
                        <input type="hidden" name="tab" value="{{ $tab }}">
                        @if($tab === 'pending')
                            <div class="form-check me-2">
                                <input class="form-check-input" type="checkbox" name="incomplete" value="1"
                                    id="includeIncomplete" {{ $includeIncomplete ? 'checked' : '' }}
                                    onchange="this.form.submit()">
                                <label class="form-check-label" for="includeIncomplete">
                                    {{ __('Include incomplete accounts') }}
                                </label>
                            </div>
                        @endif
                        <div class="input-group">
                            @if($keyword)
                                <a class="btn btn-outline-secondary" href="{{ route('signup-gifts.index', ['tab' => $tab, 'incomplete' => $includeIncomplete ? 1 : null]) }}">
                                    <i class="fa fa-times"></i>
                                </a>
                            @endif
                            <input type="text" name="keyword" class="form-control" value="{{ $keyword }}"
                                placeholder="{{ __('Search name, phone, email...') }}" autocomplete="off">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-search"></i>
                            </button>
                        </div>
                    </form>

                    @if($tab === 'pending' && $users->count() > 0)
                        <button type="button" class="btn btn-success" id="bulkGrantBtn" disabled
                            data-bs-toggle="modal" data-bs-target="#bulkGrantModal">
                            <i class="fas fa-gift me-1"></i>{{ __('Give gift to selected') }}
                            <span class="badge bg-light text-success ms-1" id="selectedCount">0</span>
                        </button>
                    @endif
                </div>

                @if($tab === 'pending' && $giftAmount <= 0 && $users->count() > 0)
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        {{ __('Set a gift amount above before you can grant it.') }}
                    </div>
                @endif

                @if($users->count() === 0)
                    <div class="text-center py-5">
                        <span data-feather="gift" style="width:48px;height:48px;" class="text-muted mb-3"></span>
                        <h5 class="text-muted mb-1">
                            @if($keyword)
                                {{ __('No users match your search.') }}
                            @elseif($tab === 'pending')
                                {{ __('Everyone who should have a gift already received one.') }}
                            @else
                                {{ __('No signup gifts have been given yet.') }}
                            @endif
                        </h5>
                    </div>
                @else
                    <form id="bulkGrantForm" method="POST" action="{{ route('signup-gifts.grant-bulk') }}">
                        @csrf
                        <input type="hidden" name="amount" id="bulkGrantAmountInput" value="{{ $giftAmount }}">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        @if($tab === 'pending')
                                            <th style="width:40px;">
                                                <input type="checkbox" class="form-check-input" id="selectAll">
                                            </th>
                                        @endif
                                        <th>{{ __('User') }}</th>
                                        <th>{{ __('Phone') }}</th>
                                        <th>{{ __('Wallet') }}</th>
                                        @if($tab === 'granted')
                                            <th>{{ __('Gift amount') }}</th>
                                            <th>{{ __('Given by') }}</th>
                                            <th>{{ __('Given at') }}</th>
                                        @else
                                            <th>{{ __('Joined') }}</th>
                                            <th class="text-end">{{ __('Actions') }}</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                        <tr>
                                            @if($tab === 'pending')
                                                <td>
                                                    <input type="checkbox" class="form-check-input user-checkbox"
                                                        name="user_ids[]" value="{{ $user->id }}">
                                                </td>
                                            @endif
                                            <td>
                                                <a href="{{ route('users.show', $user) }}" class="fw-semibold text-decoration-none">
                                                    {{ $user->name ?: __('Unnamed user') }}
                                                </a>
                                                @if($user->email)
                                                    <div class="small text-muted">{{ $user->email }}</div>
                                                @endif
                                            </td>
                                            <td>{{ $user->phone ?: '—' }}</td>
                                            <td>{{ number_format((float) $user->wallet, 2) }}</td>
                                            @if($tab === 'granted')
                                                <td>
                                                    <span class="badge bg-success-subtle text-success">
                                                        {{ number_format((float) $user->signup_gift_amount, 2) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($user->signupGiftGrantedBy)
                                                        {{ $user->signupGiftGrantedBy->name ?: __('Admin') }}
                                                    @else
                                                        <span class="text-muted">{{ __('Automatic') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{ optional($user->signup_gift_received_at)->format('Y-m-d H:i') }}
                                                    <div class="small text-muted">{{ optional($user->signup_gift_received_at)->diffForHumans() }}</div>
                                                </td>
                                            @else
                                                <td>
                                                    {{ optional($user->created_at)->format('Y-m-d') }}
                                                    <div class="small text-muted">{{ optional($user->created_at)->diffForHumans() }}</div>
                                                </td>
                                                <td class="text-end">
                                                    <button type="button"
                                                        class="btn btn-success btn-sm grant-one-btn"
                                                        data-grant-url="{{ route('signup-gifts.grant', $user) }}"
                                                        data-user-name="{{ $user->name ?: ($user->phone ?: '#'.$user->id) }}"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#grantOneModal">
                                                        <i class="fas fa-gift me-1"></i>{{ __('Give gift') }}
                                                    </button>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </form>
                    <div class="mt-3">
                        {{ $users->links('pagination::custom') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Grant one --}}
    <div class="modal fade" id="grantOneModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" id="grantOneForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-gift me-2"></i>{{ __('Give signup gift') }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">
                            {{ __('This will add the gift to the wallet of') }}
                            <strong id="grantOneName"></strong>.
                        </p>
                        <label class="form-label fw-semibold">{{ __('Amount') }}</label>
                        <div class="input-group">
                            <input type="number" name="amount" id="grantOneAmount" class="form-control"
                                min="0.01" max="999999" step="0.01" value="{{ $giftAmount }}" required>
                            <span class="input-group-text">{{ __('JOD') }}</span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-gift me-1"></i>{{ __('Give gift') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Bulk grant --}}
    <div class="modal fade" id="bulkGrantModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-gifts me-2"></i>{{ __('Give gift to selected users') }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>
                        {{ __('This will add the gift to') }}
                        <strong id="bulkGrantCountLabel">0</strong>
                        {{ __('selected users who have not received it yet.') }}
                    </p>
                    <label class="form-label fw-semibold">{{ __('Amount') }}</label>
                    <div class="input-group">
                        <input type="number" id="bulkGrantAmountField" class="form-control"
                            min="0.01" max="999999" step="0.01" value="{{ $giftAmount }}" required>
                        <span class="input-group-text">{{ __('JOD') }}</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                    <button type="button" class="btn btn-success" id="confirmBulkGrant">
                        <i class="fas fa-gift me-1"></i>{{ __('Give gifts') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const enabled = document.getElementById('signup_gift_enabled');
        const enabledLabel = document.getElementById('signup_gift_enabled_label');
        if (enabled && enabledLabel) {
            enabled.addEventListener('change', function () {
                enabledLabel.textContent = this.checked
                    ? @json(__('Enabled'))
                    : @json(__('Disabled'));
            });
        }

        const selectAll = document.getElementById('selectAll');
        const checkboxes = () => document.querySelectorAll('.user-checkbox');
        const bulkBtn = document.getElementById('bulkGrantBtn');
        const selectedCount = document.getElementById('selectedCount');

        function refreshSelection() {
            const boxes = [...checkboxes()];
            const checked = boxes.filter(box => box.checked);
            if (selectedCount) selectedCount.textContent = checked.length;
            if (bulkBtn) bulkBtn.disabled = checked.length === 0;
            if (selectAll) {
                selectAll.checked = boxes.length > 0 && checked.length === boxes.length;
                selectAll.indeterminate = checked.length > 0 && checked.length < boxes.length;
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                checkboxes().forEach(box => { box.checked = selectAll.checked; });
                refreshSelection();
            });
        }
        checkboxes().forEach(box => box.addEventListener('change', refreshSelection));

        document.querySelectorAll('.grant-one-btn').forEach(button => {
            button.addEventListener('click', function () {
                document.getElementById('grantOneForm').action = this.dataset.grantUrl;
                document.getElementById('grantOneName').textContent = this.dataset.userName;
            });
        });

        const confirmBulk = document.getElementById('confirmBulkGrant');
        if (confirmBulk) {
            confirmBulk.addEventListener('click', function () {
                const amount = document.getElementById('bulkGrantAmountField').value;
                document.getElementById('bulkGrantAmountInput').value = amount;
                document.getElementById('bulkGrantForm').submit();
            });
        }

        const bulkModal = document.getElementById('bulkGrantModal');
        if (bulkModal) {
            bulkModal.addEventListener('show.bs.modal', function () {
                const count = [...checkboxes()].filter(box => box.checked).length;
                document.getElementById('bulkGrantCountLabel').textContent = count;
            });
        }
    });
</script>
@endpush
