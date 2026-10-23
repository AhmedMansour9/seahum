@extends('web-views.seafood.auth-layout')

@section('title', translate('Reset_Password'))

@section('auth_heading', translate('reset_your_password'))

@section('auth_intro', translate('change_your_password_in_two_easy_steps.') . ' ' . translate('this_helps_to_keep_your_new_password_secure.'))

@section('auth_content')
    <form method="post" action="{{ route('customer.auth.password-recovery') }}">
        @csrf
        <input type="hidden" name="reset_token" value="{{ $token }}">
        <input type="hidden" name="identity" value="{{ request('identity') }}">

        <label class="sf-form-label" for="reset-password">{{ translate('new_password') }}<span class="sf-required">*</span></label>
        <div class="sf-input-group">
            <input class="sf-filter-field" id="reset-password" name="password" type="password" minlength="6" required
                   autocomplete="new-password" placeholder="{{ translate('enter_new_password') }}">
            @include('web-views.seafood.partials._password-toggle', ['toggleTarget' => 'reset-password'])
        </div>

        <label class="sf-form-label" for="reset-password-confirm" style="margin-top: 18px;">{{ translate('confirm_password') }}<span class="sf-required">*</span></label>
        <div class="sf-input-group">
            <input class="sf-filter-field" id="reset-password-confirm" name="confirm_password" type="password" minlength="6" required
                   autocomplete="new-password" placeholder="{{ translate('enter_confirm_password') }}">
            @include('web-views.seafood.partials._password-toggle', ['toggleTarget' => 'reset-password-confirm'])
        </div>
        <span class="sf-field-hint">{{ translate('please_provide_valid_password.') }}</span>

        <button class="sf-btn-block" type="submit" style="margin-top: 22px;">{{ translate('reset_password') }}</button>
    </form>
@endsection
