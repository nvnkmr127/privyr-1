<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Webkul\User\Models\Role;
use Webkul\User\Models\User;

abstract class TestCase extends BaseTestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        \Webkul\Lead\Models\Lead::unguard();
    }

    public function loginAsAdmin(): User
    {
        $admin = getDefaultAdmin();
        if (! $admin) {
            $role = Role::first();
            if (! $role) {
                $role = Role::create([
                    'name' => 'Administrator',
                    'description' => 'Admin role',
                    'permission_type' => 'all',
                ]);
            }
            $admin = User::first() ?? User::create([
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'role_id' => $role->id,
                'status' => 1,
            ]);
        }
        $this->actingAs($admin, 'user');

        return $admin;
    }
}
