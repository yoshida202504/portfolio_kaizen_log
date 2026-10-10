<?php

namespace Tests\Feature;

use Tests\TestCase;

class GuestRedirectTest extends TestCase
{
    public function test_guest_is_redirected_to_login_page(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }
}
