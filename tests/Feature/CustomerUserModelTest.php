<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;

class CustomerUserModelTest extends TestCase
{
    /**
     * A declared class property shadows Eloquent's magic accessor, so the
     * account pages crashed reading the customer's email.
     */
    public function test_email_is_read_from_the_model_attributes(): void
    {
        $customer = new User(['email' => 'buyer@gmail.com']);

        $this->assertSame('buyer@gmail.com', $customer->email);
        $this->assertSame('buyer@gmail.com', $customer->getAttributes()['email']);
    }
}
