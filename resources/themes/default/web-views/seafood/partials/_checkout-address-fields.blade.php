{{--
    Address fields shared by the shipping and billing forms. Field names and ids are the contract
    with SystemController::getChooseShippingAddressOther, so they are passed in, not derived.
--}}
@php
    $addressKind = $addressKind ?? 'shipping';
    $isBilling = $addressKind === 'billing';
    $isFirstAddress = $savedAddresses->count() === 0;
    $defaultLocation = getWebConfig(name: 'default_location');
    $fieldNames = $isBilling ? [
        'name' => 'billing_contact_person_name', 'phone' => 'billing_phone', 'email' => 'billing_contact_email',
        'type' => 'billing_address_type', 'city' => 'billing_city', 'zip' => 'billing_zip',
        'address' => 'billing_address', 'latitude' => 'billing_latitude', 'longitude' => 'billing_longitude',
        'method' => 'billing_method_id', 'save' => 'save_address_billing', 'update' => 'update_billing_address',
    ] : [
        'name' => 'contact_person_name', 'phone' => 'phone', 'email' => 'email',
        'type' => 'address_type', 'city' => 'city', 'zip' => 'zip',
        'address' => 'address', 'latitude' => 'latitude', 'longitude' => 'longitude',
        'method' => 'shipping_method_id', 'save' => 'save_address', 'update' => 'update_address',
    ];
    $fieldIds = $isBilling ? array_merge($fieldNames, ['save' => 'save_address_billing']) : [
        'name' => 'name', 'phone' => 'phone', 'email' => 'email', 'type' => 'address_type',
        'city' => 'city', 'zip' => $zipRestrictStatus ? 'select2-zip-container' : 'zip', 'address' => 'address',
        'latitude' => 'latitude', 'longitude' => 'longitude', 'method' => 'shipping_method_id', 'save' => 'save_address',
    ];
    if ($isBilling && $zipRestrictStatus) {
        $fieldIds['zip'] = 'billing_zip';
    }
@endphp

@if(!$isFirstAddress)
    <ul class="sf-choice-list" role="radiogroup" aria-label="{{ translate('saved_address') }}">
        @foreach($savedAddresses as $savedAddress)
            <li>
                <label class="sf-choice">
                    <input type="radio" name="sf_{{ $addressKind }}_address_choice" class="sf-address-choice"
                           data-address-kind="{{ $addressKind }}" data-address="{{ json_encode($savedAddress->only(['id', 'contact_person_name', 'phone', 'email', 'address_type', 'country', 'city', 'zip', 'address', 'latitude', 'longitude'])) }}"
                           @checked($loop->first)>
                    <span class="sf-choice-dot" aria-hidden="true"></span>
                    <span class="sf-choice-body">
                        <span class="sf-choice-title">{{ translate($savedAddress->address_type) }}</span>
                        <span class="sf-choice-text">{{ $savedAddress->contact_person_name }} · <bdi dir="ltr">{{ $savedAddress->phone }}</bdi></span>
                        <span class="sf-choice-text">{{ $savedAddress->address }}</span>
                    </span>
                </label>
            </li>
        @endforeach
        <li>
            <label class="sf-choice">
                <input type="radio" name="sf_{{ $addressKind }}_address_choice" class="sf-address-choice" data-address-kind="{{ $addressKind }}" data-address="">
                <span class="sf-choice-dot" aria-hidden="true"></span>
                <span class="sf-choice-body">
                    <span class="sf-choice-title">{{ translate('add_new_address') }}</span>
                    <span class="sf-choice-text">{{ translate('use_a_different_address_for_this_order') }}</span>
                </span>
            </label>
        </li>
    </ul>
@endif

<div class="sf-form-grid">
    <div>
        <label class="sf-form-label" for="{{ $fieldIds['name'] }}">{{ translate('contact_person_name') }} <span class="sf-required">*</span></label>
        <input type="text" class="sf-filter-field" name="{{ $fieldNames['name'] }}" id="{{ $fieldIds['name'] }}" autocomplete="name" @required($isFirstAddress)>
    </div>
    <div>
        <label class="sf-form-label" for="{{ $fieldIds['phone'] }}">{{ translate('phone') }} <span class="sf-required">*</span></label>
        <input type="tel" class="sf-filter-field" name="{{ $fieldNames['phone'] }}" id="{{ $fieldIds['phone'] }}" autocomplete="tel" dir="ltr" @required($isFirstAddress)>
    </div>

    @unless(auth('customer')->check())
        <div class="sf-form-full">
            <label class="sf-form-label" for="{{ $fieldIds['email'] }}">{{ translate('email') }} <span class="sf-required">*</span></label>
            <input type="email" class="sf-filter-field" name="{{ $fieldNames['email'] }}" id="{{ $fieldIds['email'] }}" autocomplete="email" dir="ltr" @required($isFirstAddress)>
        </div>
    @endunless

    <div>
        <label class="sf-form-label" for="{{ $fieldIds['type'] }}">{{ translate('address_type') }}</label>
        <select class="sf-filter-field" name="{{ $fieldNames['type'] }}" id="{{ $fieldIds['type'] }}">
            <option value="permanent">{{ translate('permanent') }}</option>
            <option value="home">{{ translate('home') }}</option>
            <option value="office">{{ translate('office') }}</option>
        </select>
    </div>
    <div>
        <label class="sf-form-label" for="{{ $fieldIds['city'] }}">{{ translate('city') }} <span class="sf-required">*</span></label>
        <input type="text" class="sf-filter-field" name="{{ $fieldNames['city'] }}" id="{{ $fieldIds['city'] }}" autocomplete="address-level2" @required($isFirstAddress)>
    </div>
    <div>
        <label class="sf-form-label" for="{{ $fieldIds['zip'] }}">{{ translate('zip_code') }} <span class="sf-required">*</span></label>
        @if($zipRestrictStatus)
            <select class="sf-filter-field" name="{{ $fieldNames['zip'] }}" id="{{ $fieldIds['zip'] }}" @required(!$isBilling)>
                @forelse($zipCodes as $zipCode)
                    <option value="{{ $zipCode->zipcode }}">{{ $zipCode->zipcode }}</option>
                @empty
                    <option value="">{{ translate('no_zip_to_deliver') }}</option>
                @endforelse
            </select>
        @else
            <input type="text" class="sf-filter-field" name="{{ $fieldNames['zip'] }}" id="{{ $fieldIds['zip'] }}" autocomplete="postal-code" dir="ltr" @required($isFirstAddress)>
        @endif
    </div>
    <div class="sf-form-full">
        <label class="sf-form-label" for="{{ $fieldIds['address'] }}">{{ translate('address') }} <span class="sf-required">*</span></label>
        <textarea class="sf-filter-field" name="{{ $fieldNames['address'] }}" id="{{ $fieldIds['address'] }}" autocomplete="street-address" style="min-height: 96px;" @required($isFirstAddress)></textarea>
        <span class="sf-map-alert map-address-alert">{{ translate('note') }}: {{ translate('you_need_to_select_address_from_your_selected_country') }}</span>
    </div>

    @if(getWebConfig('map_api_status') == 1)
        <div class="sf-form-full {{ $isBilling ? 'location-map-billing-canvas-area' : 'location-map-canvas-area' }}">
            <input id="{{ $isBilling ? 'pac-input-billing' : 'pac-input' }}" class="sf-filter-field sf-map-search" type="text"
                   title="{{ translate('search_your_location_here') }}" placeholder="{{ translate('search_here') }}">
            <div class="sf-map-canvas" id="{{ $isBilling ? 'location_map_canvas_billing' : 'location_map_canvas' }}"></div>
        </div>
    @endif

    <input type="hidden" name="{{ $fieldNames['latitude'] }}" id="{{ $fieldIds['latitude'] }}" value="{{ $defaultLocation ? $defaultLocation['lat'] : 0 }}">
    <input type="hidden" name="{{ $fieldNames['longitude'] }}" id="{{ $fieldIds['longitude'] }}" value="{{ $defaultLocation ? $defaultLocation['lng'] : 0 }}">
    <input type="hidden" name="{{ $fieldNames['method'] }}" id="{{ $fieldIds['method'] }}" value="0" class="sf-address-method-id">

    @if(auth('customer')->check())
        <div class="sf-form-full">
            <label class="sf-check">
                <input type="checkbox" class="sf-address-save" id="{{ $fieldIds['save'] }}" name="{{ $fieldNames['save'] }}"
                       data-save-name="{{ $fieldNames['save'] }}" data-update-name="{{ $fieldNames['update'] }}"
                       data-save-label="{{ translate('save_this_Address') }}" data-update-label="{{ translate('Update_this_Address') }}" checked>
                <span class="sf-check-box" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"></path></svg></span>
                <span class="sf-check-label sf-address-save-label">{{ translate('save_this_Address') }}</span>
            </label>
        </div>
    @endif
</div>
