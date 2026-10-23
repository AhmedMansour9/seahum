@extends('layouts.front-end.seafood-account')

@section('title', translate('my_Address'))

@section('page_title', translate('Update_Addresses'))

@push('css_or_js')
    <link rel="stylesheet" href="{{ theme_asset(path: 'public/assets/front-end/plugin/intl-tel-input/css/intlTelInput.css') }}">
    @include('web-views.seafood.partials._phone-input-styles')
@endpush

@section('page_actions')
    <a href="{{ route('account-address') }}" class="sf-btn-sm">{{ translate('back') }}</a>
@endsection

@section('account_content')
    @php($currentAddressType = Str::lower($shippingAddress->address_type))

    <form class="sf-panel sf-address-form" action="{{ route('address-update') }}" method="post">
        @csrf
        <input type="hidden" name="id" value="{{ $shippingAddress->id }}">

        <div class="sf-form-grid">
            <div>
                <span class="sf-form-label">{{ translate('address_type') }}</span>
                <ul class="sf-segment">
                    @foreach(['permanent' => 'a25', 'home' => 'a50', 'office' => 'a75'] as $addressTypeValue => $addressTypeId)
                        <li>
                            <input type="radio" id="{{ $addressTypeId }}" name="address_type" value="{{ $addressTypeValue }}" @checked($currentAddressType == $addressTypeValue)>
                            <label for="{{ $addressTypeId }}">{{ translate($addressTypeValue) }}</label>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div>
                <span class="sf-form-label">{{ translate('address_for') }}</span>
                <ul class="sf-segment">
                    <li><input type="radio" id="b25" name="is_billing" value="0" @checked($shippingAddress->is_billing == '0')><label for="b25">{{ translate('shipping') }}</label></li>
                    <li><input type="radio" id="b50" name="is_billing" value="1" @checked($shippingAddress->is_billing == '1')><label for="b50">{{ translate('billing') }}</label></li>
                </ul>
            </div>

            <div>
                <label class="sf-form-label" for="person_name">{{ translate('contact_person_name') }}<span class="sf-required">*</span></label>
                <input class="sf-filter-field" type="text" id="person_name" name="name" value="{{ $shippingAddress->contact_person_name }}" required>
            </div>

            <div>
                <label class="sf-form-label" for="own_phone">{{ translate('phone') }}<span class="sf-required">*</span></label>
                <input class="sf-filter-field phone-input-with-country-picker" type="tel" id="own_phone" name="phone" value="{{ $shippingAddress->phone }}" required>
            </div>

            <div>
                <label class="sf-form-label" for="city">{{ translate('city') }}<span class="sf-required">*</span></label>
                <input class="sf-filter-field" type="text" id="city" name="city" value="{{ $shippingAddress->city }}" required>
            </div>

            <div>
                <label class="sf-form-label" for="zip_code">{{ translate('zip_code') }}@if($zip_restrict_status)<span class="sf-required">*</span>@endif</label>
                @if($zip_restrict_status)
                    <select name="zip" id="zip_code" class="sf-filter-field" required>
                        @foreach($delivery_zipcodes as $deliveryZipcode)
                            <option value="{{ $deliveryZipcode->zipcode }}" @selected($deliveryZipcode->zipcode == $shippingAddress->zip)>{{ $deliveryZipcode->zipcode }}</option>
                        @endforeach
                    </select>
                @else
                    <input class="sf-filter-field" type="text" id="zip_code" name="zip" value="{{ $shippingAddress->zip }}" dir="ltr">
                @endif
            </div>

            <div class="sf-form-full">
                <label class="sf-form-label" for="address">{{ translate('address') }}<span class="sf-required">*</span></label>
                <textarea class="sf-filter-field" id="address" name="address" required>{{ $shippingAddress->address }}</textarea>
                <span class="sf-map-alert map-address-alert">{{ translate('note') }}: {{ translate('you_need_to_select_address_from_your_selected_country') }}</span>
            </div>

            @if(getWebConfig('map_api_status') == 1)
                <div class="sf-form-full location-map-address-canvas-area">
                    <label class="sf-form-label" for="pac-input">{{ translate('search_your_location_here') }}</label>
                    <input id="pac-input" class="sf-filter-field" type="text" style="margin-bottom: 10px;"
                           title="{{ translate('search_your_location_here') }}" placeholder="{{ translate('search_here') }}">
                    <div class="sf-map-canvas" id="location_map_canvas"></div>
                </div>
            @endif
        </div>

        <input type="hidden" id="latitude" name="latitude" value="{{ $shippingAddress->latitude ?? 0 }}" readonly required>
        <input type="hidden" id="longitude" name="longitude" value="{{ $shippingAddress->longitude ?? 0 }}" readonly required>

        <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 22px;">
            <button type="submit" class="sf-btn-block" style="flex: 1 1 200px; width: auto;">{{ translate('update') }}</button>
            <a href="{{ route('account-address') }}" class="sf-btn-outline" style="flex: 1 1 140px; width: auto;">{{ translate('close') }}</a>
        </div>
    </form>

    <span id="system-country-restrict-status" data-value="{{ $country_restrict_status }}"></span>
@endsection

@push('script')
    @include('web-views.seafood.partials._phone-input-scripts')

    @include('web-views.seafood.partials._address-map-scripts', ['addressMapCenter' => [
        'lat' => $shippingAddress->latitude ?: -33.8688,
        'lng' => $shippingAddress->longitude ?: 151.2195,
    ]])
@endpush
