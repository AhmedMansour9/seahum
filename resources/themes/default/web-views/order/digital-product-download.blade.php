@extends('layouts.front-end.seafood')

@section('title', translate('download_Digital_Product'))

@push('css_or_js')
    @include('web-views.seafood.partials._styles')
    @include('web-views.seafood.partials._track-order-styles')

    <meta property="og:image" content="{{ $web_config['web_logo']['path'] }}"/>
    <meta property="og:title" content="{{ $web_config['company_name'] }} "/>
    <meta property="og:url" content="{{ config('app.url') }}">
    <meta property="og:description" content="{{ $web_config['meta_description'] }}">
    <meta property="twitter:card" content="{{ $web_config['web_logo']['path'] }}"/>
    <meta property="twitter:title" content="{{ $web_config['company_name'] }}"/>
    <meta property="twitter:url" content="{{ config('app.url') }}">
    <meta property="twitter:description" content="{{ $web_config['meta_description'] }}">
@endpush

@section('content')
    <div style="background: #FFFFFF;">

        @include('web-views.seafood.partials._topbar')

        @include('web-views.seafood.partials._header', ['activeNavKey' => 'digital_download'])

        <main>
            <section class="sf-gutter" style="max-width: 1240px; margin: 0 auto; padding: clamp(24px, 3.5vw, 36px) 20px clamp(44px, 7vw, 86px);">

                @include('web-views.seafood.partials._breadcrumb', [
                    'breadcrumbItems' => [['label' => translate('download_Digital_Product')]],
                ])

                <div class="sf-card" style="max-width: 880px; margin: 0 auto; padding: clamp(20px, 4vw, 34px);">
                    <header style="text-align: center; margin-bottom: 24px;">
                        <span class="sf-empty-icon" style="display: inline-grid; margin: 0;" aria-hidden="true">
                            <svg width="27" height="27" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4v11m0 0-4-4m4 4 4-4"></path><path d="M5 19h14"></path></svg>
                        </span>
                        <h1 style="font-family: Cairo, sans-serif; font-size: clamp(21px, 3vw, 26px); font-weight: 800; line-height: 1.35; margin: 14px 0 0; color: #0B2C3F;">{{ translate('download_your_product') }}</h1>
                    </header>

                    <form action="{{ route('digital-product-download-pos.index') }}" method="get" class="sf-track-form">
                        <div>
                            <label class="sf-form-label" for="digital-order-id">{{ translate('order_ID') }}<span class="sf-required">*</span></label>
                            <input class="sf-filter-field" type="text" inputmode="numeric" id="digital-order-id" name="order_id" required dir="ltr"
                                   value="{{ request('order_id') }}" placeholder="{{ translate('order_ID') }}">
                        </div>
                        <div>
                            <label class="sf-form-label" for="digital-order-email">{{ translate('email_address') }}<span class="sf-required">*</span></label>
                            <input class="sf-filter-field" type="email" id="digital-order-email" name="email" required autocomplete="email" dir="ltr"
                                   value="{{ request('email') }}" placeholder="{{ translate('email_address') }}">
                        </div>
                        <button class="sf-btn-block" type="submit">{{ isset($order) ? translate('verified') : translate('verify') }}</button>
                    </form>

                    @if(isset($orderDetails))
                        <div style="margin-top: 24px;">
                            @if($isDigitalProductExist == 0)
                                <p class="sf-summary-saved" style="margin: 0;">{{ translate('you_have_no_digital_products_in_your_order') }}</p>
                            @elseif($isDigitalProductReadyCount == 0)
                                <p class="sf-summary-saved" style="margin: 0; line-height: 1.85;">
                                    {{ translate('your_digital_product_is_ready.') }}
                                    {{ translate('once_the_seller_has_uploaded_the_product__you_will_be_able_to_download_here_by_using_your_order_info.') }}
                                    {{ translate('if_you_face_any_issue_during_download_please_until_wait_or_contact_admin_via') }}
                                    <a class="sf-text-link" style="text-decoration: underline;" href="{{ auth('customer')->check() ? route('account-tickets') : route('customer.auth.login') }}">{{ translate('support_ticket') }}</a>
                                </p>
                            @else
                                <ul class="sf-order-items">
                                    @foreach($orderDetails as $orderDetail)
                                        @php
                                            $productDetails = json_decode($orderDetail->product_details, true);
                                            $digitalFile = null;
                                            if (($productDetails['digital_product_type'] ?? null) == 'ready_product') {
                                                $digitalFile = storageLink('product/digital-product', $productDetails['digital_file_ready'], ($productDetails['storage_path'] ?? 'public'));
                                            } elseif (($productDetails['digital_product_type'] ?? null) == 'ready_after_sell' && $orderDetail->digital_file_after_sell) {
                                                $digitalFile = $orderDetail->digital_file_after_sell_full_url;
                                            }
                                            $isDigitalFileAvailable = $digitalFile && $digitalFile['status'] == 200;
                                        @endphp
                                        <li class="sf-order-item">
                                            <a class="sf-row-media" href="{{ route('product', $orderDetail->product->slug) }}">
                                                <img src="{{ getStorageImages(path: $orderDetail->product->thumbnail_full_url, type: 'product') }}" alt="" loading="lazy">
                                            </a>
                                            <div class="sf-row-body">
                                                <h2 class="sf-row-title"><a href="{{ route('product', $orderDetail->product->slug) }}" style="color: inherit;">{{ $orderDetail->product->name }}</a></h2>
                                                @if($orderDetail->product->product_type != 'digital')
                                                    <span class="sf-row-meta">{{ translate('physical') }}</span>
                                                @endif
                                            </div>
                                            @if($orderDetail->product->product_type == 'digital')
                                                <div class="sf-order-item-side">
                                                    @if($isDigitalFileAvailable)
                                                        <a class="sf-btn-sm sf-btn-sm-primary" href="{{ $digitalFile['path'] }}" download target="_blank" rel="noopener">
                                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v11m0 0-4-4m4 4 4-4"></path><path d="M5 19h14"></path></svg>
                                                            {{ translate('download') }}
                                                        </a>
                                                    @else
                                                        <button type="button" class="sf-btn-sm" disabled
                                                                title="{{ $digitalFile ? translate('File_not_found') : translate('product_not_uploaded_yet') }}">{{ translate('download') }}</button>
                                                        <span class="sf-field-hint" style="margin: 0;">{{ $digitalFile ? translate('File_not_found') : translate('product_not_uploaded_yet') }}</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endif
                </div>
            </section>
        </main>

        <div style="background: #F4FAFB; border-top: 1px solid #E4EFF1; padding-top: clamp(44px, 7vw, 76px);">
            @include('web-views.seafood.partials._help-links')
        </div>

        @include('web-views.seafood.partials._footer')

        @include('web-views.seafood.partials._mobile-bar')
    </div>
@endsection

@push('script')
    @include('web-views.seafood.partials._scripts')
@endpush
