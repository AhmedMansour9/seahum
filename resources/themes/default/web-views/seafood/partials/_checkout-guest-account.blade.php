<div class="sf-panel">
    <label class="sf-check" style="min-height: 0;">
        <input type="checkbox" id="is_check_create_account" name="is_check_create_account" value="1">
        <span class="sf-check-box" aria-hidden="true"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"></path></svg></span>
        <span class="sf-check-label" style="font-weight: 700; color: #0B2C3F;">{{ translate('Create_an_account_with_the_above_info') }}</span>
    </label>

    <div class="sf-form-grid sf-guest-password-group" style="margin-top: 16px;" hidden>
        <div>
            <label class="sf-form-label" for="customer_password">{{ translate('new_Password') }}</label>
            <input type="password" class="sf-filter-field" name="customer_password" id="customer_password" autocomplete="new-password">
        </div>
        <div>
            <label class="sf-form-label" for="customer_confirm_password">{{ translate('confirm_Password') }}</label>
            <input type="password" class="sf-filter-field" name="customer_confirm_password" id="customer_confirm_password" autocomplete="new-password">
        </div>
    </div>
</div>
