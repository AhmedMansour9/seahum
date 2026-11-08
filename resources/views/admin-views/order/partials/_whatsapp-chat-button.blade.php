@php($whatsAppChatUrl = getWhatsAppChatUrl(phone: $phone ?? null, message: isset($order) ? getOrderWhatsAppMessage(order: $order) : null))
@if($whatsAppChatUrl)
    <a href="{{ $whatsAppChatUrl }}" target="_blank" rel="noopener noreferrer"
       class="d-inline-flex align-items-center flex-shrink-0"
       data-bs-toggle="tooltip" data-bs-title="{{ translate('chat_on_whatsapp') }}"
       aria-label="{{ translate('chat_on_whatsapp') }}">
        <img width="18" height="18" src="{{ dynamicAsset(path: 'public/assets/back-end/img/whatsapp.png') }}" alt="WhatsApp">
    </a>
@endif
