<?php

namespace Tests\Unit;

use App\Enums\Role;
use App\Models\User;
use Tests\TestCase;

class UserRoleTest extends TestCase
{
    public function test_admin_role_is_recognized(): void
    {
        $user = new User();
        $user->role = Role::Admin;

        $this->assertTrue($user->isAdmin());
    }

    public function test_regular_user_is_not_admin(): void
    {
        $user = new User();
        $user->role = Role::User;

        $this->assertFalse($user->isAdmin());
    }
}