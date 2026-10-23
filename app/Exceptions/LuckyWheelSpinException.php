<?php

namespace App\Exceptions;

use Exception;

class LuckyWheelSpinException extends Exception
{
    public const DISABLED = 'lucky_wheel_disabled';
    public const ALREADY_SPUN = 'lucky_wheel_already_spun';
    public const ALREADY_SPUN_TODAY = 'lucky_wheel_already_spun_today';
    public const NO_REWARDS_AVAILABLE = 'lucky_wheel_no_rewards_available';

    public function __construct(public readonly string $reason)
    {
        parent::__construct($reason);
    }
}
