@extends('layouts.admin.app')

@section('title', translate('lucky_wheel'))

@section('content')
    @php($onceFrequency = \App\Enums\LuckyWheel\LuckyWheelSpinFrequency::Once)
    @php($dailyFrequency = \App\Enums\LuckyWheel\LuckyWheelSpinFrequency::Daily)
    <div class="content container-fluid">
        <div class="mb-3 mb-sm-20">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                {{ translate('lucky_wheel') }}
            </h2>
        </div>

        <form action="{{ route('admin.lucky-wheel.settings') }}" method="post">
            @csrf
            <div class="card">
                <div class="card-body d-flex flex-column gap-3 gap-sm-20">
                    <div class="p-12 p-sm-20 bg-section rounded">
                        <h2 class="text-capitalize">{{ translate('lucky_wheel_settings') }}</h2>
                        <p class="mb-0">{{ translate('control_whether_customers_can_spin_the_wheel_and_how_often') }}</p>
                    </div>
                    <div class="p-12 p-sm-20 bg-section rounded">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="form-label text-capitalize" for="lucky-wheel-is-enabled">
                                        {{ translate('lucky_wheel_status') }}
                                        <span class="tooltip-icon" data-bs-toggle="tooltip" data-bs-placement="right"
                                              data-bs-title="{{ translate('turning_the_wheel_off_stops_new_spins_only_gifts_already_won_are_kept') }}">
                                            <i class="fi fi-sr-info"></i>
                                        </span>
                                    </label>
                                    <label class="d-flex justify-content-between align-items-center gap-3 border rounded px-3 py-10 bg-white user-select-none">
                                        <span class="fw-medium text-dark">{{ translate('status') }}</span>
                                        <label class="switcher" for="lucky-wheel-is-enabled">
                                            <input class="switcher_input" type="checkbox" value="1" name="is_enabled"
                                                   id="lucky-wheel-is-enabled" {{ $isEnabled ? 'checked' : '' }}>
                                            <span class="switcher_control"></span>
                                        </label>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="form-label text-capitalize">{{ translate('spin_frequency') }}</label>
                                    <div class="d-flex flex-wrap gap-3 border rounded px-3 py-10 bg-white">
                                        @foreach([$dailyFrequency, $onceFrequency] as $spinFrequency)
                                            <div class="form-check d-flex gap-2 align-items-center">
                                                <input class="form-check-input" type="radio" name="spin_frequency"
                                                       id="lucky-wheel-frequency-{{ $spinFrequency->value }}"
                                                       value="{{ $spinFrequency->value }}"
                                                    {{ $currentSpinFrequency === $spinFrequency ? 'checked' : '' }}>
                                                <label class="form-check-label" for="lucky-wheel-frequency-{{ $spinFrequency->value }}">
                                                    {{ $spinFrequency === $onceFrequency ? translate('once_per_customer') : translate('once_per_day') }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <p class="fs-12 mt-2 mb-0">{{ translate('in_once_mode_only_gift_outcomes_appear_on_the_wheel_and_every_spin_wins_a_gift') }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="form-label text-capitalize" for="lucky-wheel-minimum-order-amount">
                                        {{ translate('minimum_order_amount_for_gifts') }} ({{ getCurrencySymbol(currencyCode: getCurrencyCode()) }})
                                        <span class="tooltip-icon" data-bs-toggle="tooltip" data-bs-placement="right"
                                              data-bs-title="{{ translate('won_gifts_are_added_only_to_orders_whose_item_subtotal_after_discounts_reaches_this_amount_shipping_and_tax_are_excluded') }}">
                                            <i class="fi fi-sr-info"></i>
                                        </span>
                                    </label>
                                    <input type="number" class="form-control" name="minimum_order_amount" id="lucky-wheel-minimum-order-amount"
                                           min="0" step="0.01" required value="{{ old('minimum_order_amount', round($minimumOrderAmount, 2)) }}">
                                    <p class="fs-12 mt-2 mb-0">{{ translate('it_does_not_limit_spinning_customers_keep_their_gifts_until_a_qualifying_order') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary px-4 w-120">{{ translate('save') }}</button>
                    </div>
                </div>
            </div>
        </form>

        <div class="card mt-3">
            <div class="card-body d-flex flex-column gap-20">
                <div class="d-flex justify-content-between align-items-center gap-20 flex-wrap">
                    <h3 class="mb-0">
                        {{ translate('wheel_rewards') }}
                        <span class="badge text-dark bg-body-secondary fw-semibold rounded-50">{{ $rewards->total() }}</span>
                    </h3>
                    <div class="d-flex flex-wrap gap-3 align-items-stretch">
                        <form action="{{ url()->current() }}" method="GET">
                            <div class="input-group flex-grow-1 max-w-280">
                                <input type="search" name="searchValue" class="form-control"
                                       placeholder="{{ translate('search_by_reward_name') }}"
                                       aria-label="{{ translate('search_by_reward_name') }}"
                                       value="{{ request('searchValue') }}">
                                <div class="input-group-append search-submit">
                                    <button type="submit"><i class="fi fi-rr-search"></i></button>
                                </div>
                            </div>
                        </form>
                        <a href="{{ route('admin.lucky-wheel.reward.add') }}" class="btn btn-primary">+ {{ translate('add_new') }}</a>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-borderless align-middle">
                        <thead class="text-capitalize">
                        <tr>
                            <th>{{ translate('SL') }}</th>
                            <th>{{ translate('reward') }}</th>
                            <th>{{ translate('type') }}</th>
                            <th class="text-center">{{ translate('display_order') }}</th>
                            <th class="text-center">{{ translate('chance_daily_mode') }}</th>
                            <th class="text-center">{{ translate('chance_once_mode') }}</th>
                            <th class="text-center">{{ translate('status') }}</th>
                            <th class="text-center">{{ translate('action') }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($rewards as $key => $reward)
                            <tr>
                                <td>{{ $rewards->firstItem() + $key }}</td>
                                <td>
                                    <div class="d-flex gap-2 align-items-center">
                                        <div class="avatar-60 d-flex align-items-center rounded">
                                            <img class="img-fluid h-100 object-fit-cover w-100 rounded" alt=""
                                                 src="{{ getStorageImages(path: $reward->image_full_url, type: 'backend-basic') }}">
                                        </div>
                                        <div class="overflow-hidden">
                                            <div class="fs-14 max-w-200 text-truncate" title="{{ $reward->name }}">{{ $reward->name }}</div>
                                            @foreach($reward->getNameTranslations() as $locale => $translatedName)
                                                @if($locale !== 'en')
                                                    <div class="fs-12 text-body-light max-w-200 text-truncate" title="{{ $translatedName }}">{{ $translatedName }}</div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($reward->type === \App\Enums\LuckyWheel\LuckyWheelRewardType::Gift)
                                        <span class="badge badge-success text-bg-success">{{ translate('gift') }}</span>
                                    @else
                                        <span class="badge badge-secondary text-bg-secondary">{{ translate('no_prize') }}</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $reward->display_order }}</td>
                                @foreach([$dailyFrequency, $onceFrequency] as $spinFrequency)
                                    <td class="text-center">
                                        @if(isset($selectionProbabilities[$spinFrequency->value][$reward->id]))
                                            {{ $selectionProbabilities[$spinFrequency->value][$reward->id] }}%
                                        @else
                                            <span class="text-body-light" title="{{ translate('not_on_the_wheel_in_this_mode') }}">—</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td>
                                    <form action="{{ route('admin.lucky-wheel.reward.status') }}" method="post"
                                          id="lucky-wheel-reward-status{{ $reward->id }}-form" class="no-reload-form">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $reward->id }}">
                                        <label class="switcher mx-auto" for="lucky-wheel-reward-status{{ $reward->id }}">
                                            <input class="switcher_input custom-modal-plugin" type="checkbox" value="1" name="status"
                                                   id="lucky-wheel-reward-status{{ $reward->id }}"
                                                   {{ $reward->is_active ? 'checked' : '' }}
                                                   data-modal-type="input-change-form" data-reload="true"
                                                   data-modal-form="#lucky-wheel-reward-status{{ $reward->id }}-form"
                                                   data-on-image="{{ dynamicAsset(path: 'public/assets/new/back-end/img/modal/brand-status-on.png') }}"
                                                   data-off-image="{{ dynamicAsset(path: 'public/assets/new/back-end/img/modal/brand-status-off.png') }}"
                                                   data-on-title="{{ translate('want_to_turn_on_this_reward') }}"
                                                   data-off-title="{{ translate('want_to_turn_off_this_reward') }}"
                                                   data-on-message="<p>{{ translate('if_enabled_this_reward_can_appear_on_the_wheel') }}</p>"
                                                   data-off-message="<p>{{ translate('if_disabled_this_reward_is_removed_from_new_spins_gifts_already_won_are_kept') }}</p>"
                                                   data-on-button-text="{{ translate('turn_on') }}"
                                                   data-off-button-text="{{ translate('turn_off') }}">
                                            <span class="switcher_control"></span>
                                        </label>
                                    </form>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center gap-3">
                                        <a class="btn btn-outline-info icon-btn" title="{{ translate('edit') }}"
                                           href="{{ route('admin.lucky-wheel.reward.update', ['id' => $reward->id]) }}">
                                            <i class="fi fi-sr-pencil"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger icon-btn" title="{{ translate('delete') }}"
                                                data-bs-toggle="modal" data-bs-target="#lucky-wheel-reward-delete-{{ $reward->id }}">
                                            <i class="fi fi-rr-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-lg-end">
                    {{ $rewards->links() }}
                </div>
                @if($rewards->isEmpty())
                    @include('layouts.admin.partials._empty-state', ['text' => 'no_reward_found'], ['image' => 'default'])
                @endif
            </div>
        </div>
    </div>

    @foreach($rewards as $reward)
        <div class="modal fade" id="lucky-wheel-reward-delete-{{ $reward->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0 d-flex justify-content-end">
                        <button type="button" class="btn btn-circle border-0 fs-12 text-body bg-section2 shadow-none" style="--size: 2rem;" data-bs-dismiss="modal" aria-label="{{ translate('close') }}">
                            <i class="fi fi-sr-cross"></i>
                        </button>
                    </div>
                    <form action="{{ route('admin.lucky-wheel.reward.delete') }}" method="post" class="modal-body px-20 py-0 mb-30">
                        @csrf
                        <input type="hidden" name="id" value="{{ $reward->id }}">
                        <div class="d-flex flex-column align-items-center text-center mb-30">
                            <img src="{{ dynamicAsset(path: 'public/assets/new/back-end/img/modal/delete.png') }}" width="80" class="mb-20" alt="">
                            <h2 class="modal-title mb-3">{{ translate('are_you_sure_to_delete_this_reward') }}?</h2>
                            <div class="text-center">{{ translate('it_will_be_removed_from_the_wheel_gifts_already_won_and_past_orders_are_kept') }}</div>
                        </div>
                        <div class="d-flex justify-content-center gap-3">
                            <button type="button" class="btn btn-secondary max-w-120 flex-grow-1" data-bs-dismiss="modal">{{ translate('no') }}</button>
                            <button type="submit" class="btn btn-danger max-w-120 flex-grow-1">{{ translate('yes_delete') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endsection
