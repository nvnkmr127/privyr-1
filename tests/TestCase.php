<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Webkul\User\Models\User;

abstract class TestCase extends BaseTestCase
{
    use DatabaseTransactions;

    public function loginAsAdmin(): User
    {
        $admin = getDefaultAdmin();
        if (! $admin) {
            $admin = User::first() ?? User::factory()->create();
        }
        $this->actingAs($admin, 'user');

        return $admin;
    }
}
