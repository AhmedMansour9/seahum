@extends('layouts.front-end.seafood-account')

@section('title', translate('Refer_&_Earn'))

@section('page_title', translate('Refer_&_Earn'))

@section('account_content')
    @php
        $referralCode = $customer_detail->referral_code;
        $referralLink = route('home') . '?referral_code=' . $referralCode;
        $referralShareText = translate('Greetings') . ', ' . $web_config['company_name'] . ' '
            . translate('is_the_best_ecommerce_platform_in_the_country.if_you_are_new_to_this_website_dont_forget_to_use') . ' '
            . $referralCode . ' ' . translate('as_the_referral_code_while_sign_up_into.') . ' ' . $web_config['company_name'];
    @endphp

    <div class="sf-panel sf-refer">
        <div class="sf-refer-hero">
            <span class="sf-empty-icon" style="width: 72px; height: 72px; border-radius: 24px; margin: 0;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 12v8H4v-8M2 7h20v5H2zM12 20V7M12 7H7.5a2.5 2.5 0 1 1 0-5C11 2 12 7 12 7zM12 7h4.5a2.5 2.5 0 1 0 0-5C13 2 12 7 12 7z"></path></svg>
            </span>
            <h2 class="sf-page-title" style="font-size: clamp(19px, 2.4vw, 23px);">{{ translate('invite_Your_Friends_&_Businesses') }}</h2>
            <p class="sf-empty-text" style="margin: 0;">{{ translate('copy_your_code_and_share_your_friends') }}</p>
        </div>

        <div class="sf-refer-code-block">
            <span class="sf-form-label">{{ translate('your_personal_code') }}</span>
            <button type="button" class="sf-coupon-code sf-refer-code" data-sf-copy="{{ $referralCode }}"
                    aria-label="{{ translate('copy') }} {{ $referralCode }}">
                <bdi dir="ltr">{{ $referralCode }}</bdi>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="8" width="12" height="12" rx="2.5"></rect><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"></path></svg>
            </button>

            <span class="sf-divider-text" style="margin: 20px 0 14px;">{{ translate('oR_SHARE') }}</span>
            <div class="sf-refer-share">
                <a class="sf-btn-sm" target="_blank" rel="noopener" href="https://api.whatsapp.com/send?text={{ urlencode($referralShareText . ' ' . url('/')) }}">WhatsApp</a>
                <a class="sf-btn-sm" href="mailto:?subject={{ rawurlencode(translate('Refer_&_Earn')) }}&body={{ rawurlencode($referralShareText . ' ' . url('/')) }}">{{ translate('email') }}</a>
                <button type="button" class="sf-btn-sm" data-sf-copy="{{ $referralLink }}">{{ translate('copy_link') }}</button>
            </div>
        </div>
    </div>

    <div class="sf-panel">
        <h2 class="sf-panel-title">{{ translate('how_you_it_works') }}?</h2>
        <ol class="sf-stack" style="margin: 0; padding: 0; list-style: none;">
            <li class="sf-step"><span class="sf-step-num"><bdi dir="ltr">1</bdi></span><span class="sf-step-text">{{ translate('invite_your_friends_&_businesses') }}</span></li>
            <li class="sf-step"><span class="sf-step-num"><bdi dir="ltr">2</bdi></span><span class="sf-step-text">{{ translate('they_register') }} {{ $web_config['company_name'] }} {{ translate('with_special_offer') }}</span></li>
            <li class="sf-step"><span class="sf-step-num"><bdi dir="ltr">3</bdi></span><span class="sf-step-text">{{ translate('you_made_your_earning') }}</span></li>
        </ol>
    </div>
@endsection
