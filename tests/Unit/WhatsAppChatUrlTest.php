<?php

namespace Tests\Unit;

use Tests\TestCase;

class WhatsAppChatUrlTest extends TestCase
{
    public function test_egyptian_numbers_in_every_common_format_get_the_same_link(): void
    {
        foreach (['01012345678', '+201012345678', '00201012345678', '1012345678', '20 101 234 5678'] as $phone) {
            $this->assertSame('https://wa.me/201012345678', getWhatsAppChatUrl(phone: $phone), $phone);
        }
    }

    public function test_missing_or_too_short_numbers_get_no_link(): void
    {
        $this->assertNull(getWhatsAppChatUrl(phone: null));
        $this->assertNull(getWhatsAppChatUrl(phone: '123'));
    }

    public function test_guest_order_uses_the_shipping_contact_phone(): void
    {
        $order = ['is_guest' => 1, 'customer' => null, 'shipping_address_data' => (object)['phone' => '01099999999']];

        $this->assertSame('01099999999', getOrderCustomerPhone(order: $order));
    }

    public function test_registered_customer_order_uses_the_customer_phone(): void
    {
        $order = ['is_guest' => 0, 'customer' => ['phone' => '01011111111'], 'shipping_address_data' => (object)['phone' => '01099999999']];

        $this->assertSame('01011111111', getOrderCustomerPhone(order: $order));
    }
}
