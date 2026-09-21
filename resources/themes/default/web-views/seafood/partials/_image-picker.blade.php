{{-- Multi-image field: the <input> itself carries the files, so the form posts them as {{ $pickerName }} without extra JS on submit. --}}
@php
    $pickerName = $pickerName ?? 'image[]';
    $pickerId = $pickerId ?? 'sf-image-picker-' . \Illuminate\Support\Str::random(6);
    $pickerMaxCount = $pickerMaxCount ?? 5;
@endphp

<div class="sf-image-picker" data-max-count="{{ $pickerMaxCount }}">
    <ul class="sf-image-picker-list" aria-live="polite"></ul>
    <label class="sf-image-picker-add" for="{{ $pickerId }}">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="3"></rect><circle cx="9" cy="10" r="1.8"></circle><path d="m21 16-5-5-7 7M12 3v0"></path></svg>
        <span>{{ translate('add_images') }}</span>
        <span class="sf-image-picker-hint">{{ translate('up_to') }} <bdi dir="ltr">{{ $pickerMaxCount }}</bdi></span>
    </label>
    <input type="file" class="sf-image-picker-input sr-only" id="{{ $pickerId }}" name="{{ $pickerName }}" multiple
           accept="{{ getFileUploadFormats(skip: '.svg,.gif,.webp') }}" data-max-size="{{ getFileUploadMaxSize() }}">
</div>
