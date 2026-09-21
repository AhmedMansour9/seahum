<div class="sf-meta-grid">
    <span class="sf-meta-item"><span class="sf-meta-label">{{ translate('name') }}</span><span class="sf-meta-value">{{ $orderAddress->contact_person_name ?? '' }}</span></span>
    <span class="sf-meta-item"><span class="sf-meta-label">{{ translate('phone') }}</span><span class="sf-meta-value"><bdi dir="ltr">{{ $orderAddress->phone ?? '' }}</bdi></span></span>
    <span class="sf-meta-item"><span class="sf-meta-label">{{ translate('city') }} / {{ translate('zip') }}</span><span class="sf-meta-value">{{ $orderAddress->city ?? '' }}@if(!empty($orderAddress->zip)), <bdi dir="ltr">{{ $orderAddress->zip }}</bdi>@endif</span></span>
    <span class="sf-meta-item" style="grid-column: 1 / -1;">
        <span class="sf-meta-label">{{ translate('address') }}</span>
        <span class="sf-meta-value" style="font-weight: 400; font-family: Tajawal, sans-serif; line-height: 1.8; color: #34586B;">{{ $orderAddress->address ?? '' }}</span>
    </span>
</div>
