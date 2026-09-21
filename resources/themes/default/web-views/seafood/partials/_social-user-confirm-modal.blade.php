{{-- "Is it you?" prompt when a social sign-in email already belongs to an existing account. Opens on page load. --}}
@if($user && $user?->login_medium != ($socialLoginNewCustomer['login_medium'] ?? null))
    @php
        $decodedIdentity = base64_decode($identity);
        $atPosition = strpos($decodedIdentity, '@');
        if ($atPosition !== false) {
            $localPart = substr($decodedIdentity, 0, $atPosition);
            $maskedIdentity = (strlen($localPart) > 4 ? substr($localPart, 0, 4) . '****' : $localPart) . substr($decodedIdentity, $atPosition);
        } else {
            $maskedIdentity = str_repeat('*', strlen($decodedIdentity));
        }
    @endphp

    <div class="sf-modal" id="sf-social-user-modal" role="dialog" aria-modal="true" aria-labelledby="sf-social-user-title" hidden>
        <div class="sf-modal-dialog" style="max-width: 440px;">
            <div class="sf-modal-head">
                <h2 class="sf-modal-title" id="sf-social-user-title">{{ translate('Is_It_You') }}?</h2>
                <button type="button" class="sf-icon-btn" data-sf-modal-close aria-label="{{ translate('close') }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 6 12 12M18 6 6 18"></path></svg>
                </button>
            </div>
            <div class="sf-modal-body" style="text-align: center;">
                <img alt="" width="84" height="84" style="border-radius: 50%; object-fit: cover; border: 1px solid #E4EFF1;"
                     src="{{ getStorageImages(path: $user?->image_full_url ?? '', type: 'avatar') }}">
                <p style="font-family: Cairo, sans-serif; font-weight: 700; color: #0B2C3F; margin: 10px 0 0; overflow-wrap: anywhere;">{{ $user?->f_name }} {{ $user?->l_name }}</p>
                <p style="font-size: 14.5px; line-height: 1.85; color: #55747F; margin: 12px 0 20px; overflow-wrap: anywhere;">
                    {{ translate('it_looks_like_the_email') }}
                    <bdi dir="ltr" class="sf-text-link">{{ $maskedIdentity }}</bdi>
                    {{ translate('you_entered_has_already_been_used_and_has_an_existing_account') }}
                </p>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a class="sf-btn-sm sf-btn-sm-primary" style="flex: 1 1 140px;"
                       href="{{ route('customer.auth.social-login-confirmation', ['identity' => $identity, 'status' => 'approve']) }}">
                        {{ translate('Yes') }}, {{ translate('It’s_Me') }}
                    </a>
                    <button type="button" class="sf-btn-sm" data-sf-modal-close style="flex: 1 1 140px;">{{ translate('No') }}</button>
                </div>
            </div>
        </div>
    </div>

    @push('script')
        <script>
            $(function () {
                openSeafoodModal('sf-social-user-modal');
            });
        </script>
    @endpush
@endif
