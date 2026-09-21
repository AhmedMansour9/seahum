@extends('layouts.front-end.seafood-account')

@section('title', translate('my_Address'))

@section('page_title', translate('my_Address'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/css/intlTelInput.css') }}">
    @include('web-views.seafood.partials._phone-input-styles')
@endpush

@section('page_actions')
    <button type="button" class="sf-btn-sm sf-btn-sm-primary" id="add_new_address" data-sf-modal-open="exampleModal">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"></path></svg>
        {{ translate('add_address') }}
    </button>
@endsection

@section('account_content')
    @php($default_location = getWebConfig(name: 'default_location'))

    @if($shippingAddresses->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(min(280px, 100%), 1fr)); gap: 16px;">
            @foreach($shippingAddresses as $shippingAddress)
                <article class="sf-panel" style="display: flex; flex-direction: column; gap: 14px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;">
                        <span style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; min-width: 0;">
                            <span class="sf-badge">{{ translate($shippingAddress['address_type']) }}</span>
                            <span class="sf-badge sf-badge-neutral">
                                {{ $shippingAddress['is_billing'] == 1 ? translate('Billing_address') : translate('shipping_address') }}
                            </span>
                        </span>
                        <span style="display: flex; align-items: center; gap: 8px;">
                            <a class="sf-icon-btn" href="{{ route('address-edit', $shippingAddress->id) }}"
                               aria-label="{{ translate('edit_address') }}">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M5 19h14"></path><path d="M15.4 4.6a2 2 0 0 1 2.8 2.8L8.6 17H5.8v-2.8z"></path></svg>
                            </a>
                            <button type="button" class="sf-icon-btn sf-icon-btn-danger call-route-alert"
                                    data-route="{{ route('address-delete', ['id' => $shippingAddress->id]) }}"
                                    data-message="{{ translate('want_to_delete_this_address') }}?"
                                    aria-label="{{ translate('delete') }}">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M5 7h14M10 7V5h4v2M7 7l.8 12.2A1.8 1.8 0 0 0 9.6 21h4.8a1.8 1.8 0 0 0 1.8-1.8L17 7"></path></svg>
                            </button>
                        </span>
                    </div>

                    <div class="sf-meta-grid">
                        <span class="sf-meta-item">
                            <span class="sf-meta-label">{{ translate('Name') }}</span>
                            <span class="sf-meta-value">{{ $shippingAddress['contact_person_name'] }}</span>
                        </span>
                        <span class="sf-meta-item">
                            <span class="sf-meta-label">{{ translate('phone') }}</span>
                            <span class="sf-meta-value"><bdi dir="ltr">{{ $shippingAddress['phone'] }}</bdi></span>
                        </span>
                        <span class="sf-meta-item">
                            <span class="sf-meta-label">{{ translate('city') }}</span>
                            <span class="sf-meta-value">{{ $shippingAddress['city'] }}</span>
                        </span>
                        @if($shippingAddress['zip'])
                            <span class="sf-meta-item">
                                <span class="sf-meta-label">{{ translate('zip_code') }}</span>
                                <span class="sf-meta-value"><bdi dir="ltr">{{ $shippingAddress['zip'] }}</bdi></span>
                            </span>
                        @endif
                        <span class="sf-meta-item">
                            <span class="sf-meta-label">{{ translate('country') }}</span>
                            <span class="sf-meta-value">{{ $shippingAddress['country'] }}</span>
                        </span>
                    </div>

                    <span class="sf-meta-item">
                        <span class="sf-meta-label">{{ translate('address') }}</span>
                        <span class="sf-meta-value" style="font-weight: 400; font-family: Tajawal, sans-serif; line-height: 1.8; color: #34586B;">
                            {{ $shippingAddress['address'] }}
                        </span>
                    </span>
                </article>
            @endforeach
        </div>
    @else
        @include('web-views.seafood.partials._empty-state', [
            'emptyStateIcon' => '<path d="M12 21s6.5-5.4 6.5-10.2A6.5 6.5 0 0 0 5.5 10.8C5.5 15.6 12 21 12 21z"></path><circle cx="12" cy="10.6" r="2.4"></circle>',
            'emptyStateTitle' => translate('no_address_found').'!',
            'emptyStateText' => translate('add_a_delivery_address_to_make_checkout_faster'),
        ])
    @endif

    <div class="sf-modal" id="exampleModal" role="dialog" aria-modal="true" aria-labelledby="add-address-title" hidden>
        <div class="sf-modal-dialog">
            <div class="sf-modal-head">
                <h2 class="sf-modal-title" id="add-address-title">{{ translate('add_new_address') }}</h2>
                <button type="button" class="sf-icon-btn" data-sf-modal-close aria-label="{{ translate('close') }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 6 12 12M18 6 6 18"></path></svg>
                </button>
            </div>

            <form class="sf-modal-body sf-address-form" action="{{ route('address-store') }}" method="post">
                @csrf

                <div class="sf-form-grid">
                    <div>
                        <span class="sf-form-label">{{ translate('address_type') }}</span>
                        <ul class="sf-segment">
                            <li><input type="radio" id="a25" name="address_type" value="permanent" checked><label for="a25">{{ translate('permanent') }}</label></li>
                            <li><input type="radio" id="a50" name="address_type" value="home"><label for="a50">{{ translate('home') }}</label></li>
                            <li><input type="radio" id="a75" name="address_type" value="office"><label for="a75">{{ translate('office') }}</label></li>
                        </ul>
                    </div>

                    <div>
                        <span class="sf-form-label">{{ translate('address_for') }}</span>
                        <ul class="sf-segment">
                            <li><input type="radio" id="b25" name="is_billing" value="0" checked><label for="b25">{{ translate('shipping') }}</label></li>
                            <li><input type="radio" id="b50" name="is_billing" value="1"><label for="b50">{{ translate('billing') }}</label></li>
                        </ul>
                    </div>

                    <div>
                        <label class="sf-form-label" for="name">
                            {{ translate('contact_person_name') }}<span class="sf-required">*</span>
                        </label>
                        <input class="sf-filter-field" type="text" id="name" name="name"
                               placeholder="{{ translate('contact_person_name') }}" required>
                    </div>

                    <div>
                        <label class="sf-form-label" for="phone">
                            {{ translate('phone') }}<span class="sf-required">*</span>
                        </label>
                        <input class="sf-filter-field phone-input-with-country-picker" id="phone" type="tel" name="phone"
                               placeholder="{{ translate('enter_phone_number') }}" required>
                    </div>

                    <div>
                        <label class="sf-form-label" for="address-city">
                            {{ translate('city') }}<span class="sf-required">*</span>
                        </label>
                        <input class="sf-filter-field" type="text" id="address-city" name="city"
                               placeholder="{{ translate('city') }}" required>
                    </div>

                    <div>
                        <label class="sf-form-label" for="zip">
                            {{ translate('zip_code') }}@if($zip_restrict_status)<span class="sf-required">*</span>@endif
                        </label>
                        @if($zip_restrict_status)
                            <select name="zip" id="zip" class="sf-filter-field" required>
                                @foreach($zip_codes as $code)
                                    <option value="{{ $code->zipcode }}">{{ $code->zipcode }}</option>
                                @endforeach
                            </select>
                        @else
                            <input class="sf-filter-field" type="text" id="zip" name="zip"
                                   placeholder="{{ translate('zip_code') }}">
                        @endif
                    </div>

                    <div class="sf-form-full">
                        <label class="sf-form-label" for="address">
                            {{ translate('address') }}<span class="sf-required">*</span>
                        </label>
                        <textarea class="sf-filter-field" id="address" name="address"
                                  placeholder="{{ translate('address') }}" required></textarea>
                        <span class="sf-map-alert map-address-alert">
                            {{ translate('note') }}: {{ translate('you_need_to_select_address_from_your_selected_country') }}
                        </span>
                    </div>

                    @if(getWebConfig('map_api_status') == 1)
                        <div class="sf-form-full location-map-address-canvas-area">
                            <label class="sf-form-label" for="pac-input">{{ translate('search_your_location_here') }}</label>
                            <input id="pac-input" class="sf-filter-field" type="text"
                                   style="margin-bottom: 10px;"
                                   title="{{ translate('search_your_location_here') }}"
                                   placeholder="{{ translate('search_here') }}">
                            <div class="sf-map-canvas" id="location_map_canvas"></div>
                        </div>
                    @endif
                </div>

                <input type="hidden" id="latitude" name="latitude" value="{{ $default_location ? $default_location['lat'] : 0 }}" readonly required>
                <input type="hidden" id="longitude" name="longitude" value="{{ $default_location ? $default_location['lng'] : 0 }}" readonly required>

                <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 22px;">
                    <button type="submit" class="sf-btn-block" style="flex: 1 1 200px; width: auto;">{{ translate('add_information') }}</button>
                    <button type="button" class="sf-btn-outline" data-sf-modal-close style="flex: 1 1 140px; width: auto;">{{ translate('close') }}</button>
                </div>
            </form>
        </div>
    </div>

    <span id="system-country-restrict-status" data-value="{{ $country_restrict_status }}"></span>
@endsection

@push('script')
    @include('web-views.seafood.partials._phone-input-scripts')

    @include('web-views.seafood.partials._address-map-scripts', ['addressMapCenter' => getWebConfig(name: 'default_location')])
@endpush
