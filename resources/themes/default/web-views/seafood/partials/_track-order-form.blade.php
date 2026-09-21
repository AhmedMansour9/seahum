{{-- Public "track order" lookup by order ID + phone number. --}}
<form action="{{ route('track-order.result') }}" method="get" class="sf-track-form">
    <div>
        <label class="sf-form-label" for="track-order-id">{{ translate('order_id') }}<span class="sf-required">*</span></label>
        <input class="sf-filter-field" type="number" inputmode="numeric" min="1" id="track-order-id" name="order_id" required
               value="{{ $trackOrderId ?? request('order_id') }}" placeholder="{{ translate('order_id') }}" dir="ltr">
    </div>
    <div>
        <label class="sf-form-label" for="track-order-phone">{{ translate('phone_number') }}<span class="sf-required">*</span></label>
        <input class="sf-filter-field" type="tel" id="track-order-phone" name="phone_number" required autocomplete="tel"
               value="{{ $trackOrderPhone ?? request('phone_number') }}" placeholder="{{ translate('your_phone_number') }}" dir="ltr">
    </div>
    <button class="sf-btn-block" type="submit" name="trackOrder">{{ translate('track_order') }}</button>
</form>
