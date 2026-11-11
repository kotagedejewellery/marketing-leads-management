<?php

namespace Tests\Feature;

use Tests\TestCase;

class TrustedProxyTest extends TestCase
{
    public function test_login_assets_use_https_behind_the_proxy(): void
    {
        $this->withHeaders([
            'X-Forwarded-Host' => 'leadcontrol.kotagedejewellery.tech',
            'X-Forwarded-Proto' => 'https',
        ])
            ->get(route('login'))
            ->assertOk()
            ->assertSee('https://leadcontrol.kotagedejewellery.tech/build/', false);
    }
}
