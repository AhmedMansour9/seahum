<?php

namespace App\Services;

use App\Enums\OrderSource;
use App\Models\Order;
use App\Models\User;

/**
 * Connects EasyOrders guest orders to the customer account that owns the same phone number,
 * so the customer sees and tracks them in the app.
 */
class EasyOrdersCustomerLinkService
{
    private const SUBSCRIBER_NUMBER_LENGTH = 10;

    /**
     * @return list<string>
     */
    public function getPhoneVariants(?string $phone): array
    {
        $digits = preg_replace('/\D/', '', (string)$phone);
        if (strlen($digits) < self::SUBSCRIBER_NUMBER_LENGTH) {
            return [];
        }

        $subscriberNumber = substr($digits, -self::SUBSCRIBER_NUMBER_LENGTH);

        return [
            $subscriberNumber,
            '0' . $subscriberNumber,
            '20' . $subscriberNumber,
            '+20' . $subscriberNumber,
            '0020' . $subscriberNumber,
        ];
    }

    public function findCustomerIdByPhone(?string $phone): ?int
    {
        $phoneVariants = $this->getPhoneVariants(phone: $phone);
        if (empty($phoneVariants)) {
            return null;
        }

        return User::whereIn('phone', $phoneVariants)->orderBy('id')->value('id');
    }

    public function linkOrdersToCustomer(User $customer): int
    {
        $phoneVariants = $this->getPhoneVariants(phone: $customer['phone']);
        if (empty($phoneVariants)) {
            return 0;
        }

        return Order::where('order_source', OrderSource::EasyOrders->value)
            ->whereNull('customer_id')
            ->whereIn('shipping_address_data->phone', $phoneVariants)
            ->update(['customer_id' => $customer['id'], 'is_guest' => 0]);
    }
}
