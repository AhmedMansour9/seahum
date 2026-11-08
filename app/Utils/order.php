<?php


use Illuminate\Support\Str;

if (!function_exists('getOrderSummary')) {
    function getOrderSummary(object $order): array
    {
        $sub_total = 0;
        $total_tax = $order['total_tax_amount'];
        $total_discount_on_product = 0;
        foreach ($order->details as $detail) {
            $sub_total += $detail->price * $detail->qty;
            $total_discount_on_product += $detail->discount;
        }
        $total_shipping_cost = $order['shipping_cost'];
        return [
            'subtotal' => $sub_total,
            'total_tax' => $total_tax,
            'total_discount_on_product' => $total_discount_on_product,
            'total_shipping_cost' => $total_shipping_cost,
        ];
    }
}

if (!function_exists('getUniqueId')) {
    function getUniqueId(): string
    {
        return rand(1000, 9999) . '-' . Str::random(5) . '-' . time();
    }
}


if (!function_exists('getOrderStatusList')) {
    function getOrderStatusList(): array
    {
        return [
            'pending',
            'confirmed',
            'processing',
            'out_for_delivery',
            'delivered',
            'returned',
            'failed',
            'canceled',
        ];
    }
}

if (!function_exists('getOrderCustomerPhone')) {
    /**
     * Phone to reach the customer of an order: the registered customer's phone,
     * or the shipping contact phone for guest orders (e.g. EasyOrders orders).
     */
    function getOrderCustomerPhone(object|array $order): ?string
    {
        if (!$order['is_guest'] && !empty($order['customer']['phone'])) {
            return $order['customer']['phone'];
        }

        $shippingAddress = $order['shipping_address_data'] ?? null;
        if (is_string($shippingAddress)) {
            $shippingAddress = json_decode($shippingAddress);
        }

        return $shippingAddress->phone ?? null;
    }
}

if (!function_exists('getWhatsAppChatUrl')) {
    /**
     * wa.me link for a phone number. Local numbers (leading 0, or a 10 digit Egyptian mobile) get the default country code.
     */
    function getWhatsAppChatUrl(?string $phone, string $defaultCountryCode = '20', ?string $message = null): ?string
    {
        $digits = preg_replace('/\D+/', '', (string)$phone);
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '0')) {
            $digits = $defaultCountryCode . substr($digits, 1);
        } elseif ($defaultCountryCode === '20' && strlen($digits) === 10 && str_starts_with($digits, '1')) {
            $digits = $defaultCountryCode . $digits;
        }

        if (strlen($digits) < 8) {
            return null;
        }

        return 'https://wa.me/' . $digits . ($message ? '?text=' . rawurlencode($message) : '');
    }
}

if (!function_exists('getOrderWhatsAppMessage')) {
    /**
     * Order confirmation message pre-filled in WhatsApp, built from config('order_whatsapp').
     */
    function getOrderWhatsAppMessage(\App\Models\Order $order): string
    {
        $shippingAddress = $order['shipping_address_data'];
        if (is_string($shippingAddress)) {
            $shippingAddress = json_decode($shippingAddress);
        }

        $customerName = $shippingAddress->contact_person_name
            ?? trim(($order->customer['f_name'] ?? '') . ' ' . ($order->customer['l_name'] ?? ''));

        $items = $order->details->map(function ($detail) {
            $name = $detail->getInvoiceProductName() ?? translate('item');
            if (!empty($detail['variant'])) {
                $name .= ' - ' . $detail['variant'];
            }

            return strtr(config('order_whatsapp.item_line'), ['{name}' => $name, '{qty}' => $detail['qty']]);
        })->implode("\n");

        $totalAmount = \App\Utils\OrderManager::getOrderTotalPriceSummary(order: $order)['totalAmount'];
        $deliveryTo = $shippingAddress->address ?? $shippingAddress->city ?? '';

        return strtr(config('order_whatsapp.confirmation_message'), [
            '{customer_name}' => $customerName,
            '{items}' => rtrim($items),
            '{total}' => number_format(usdToDefaultCurrency(amount: $totalAmount), 2) . ' ' . config('order_whatsapp.currency_label'),
            '{delivery_to}' => $deliveryTo,
        ]);
    }
}
