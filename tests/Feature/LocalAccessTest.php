<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_remote_clients_cannot_manage_or_use_coupons(): void
    {
        $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.6']);
        $this->get('/')->assertForbidden();
        $this->get('/periksa')->assertForbidden();
        $this->post('/simulasi', ['code' => 'HEMAT20'])->assertForbidden();
    }

    public function test_non_local_hosts_and_production_are_denied(): void
    {
        $this->get('http://example.com/')->assertForbidden();
        $this->app->detectEnvironment(fn () => 'production');
        $this->get('/')->assertForbidden();
    }

    public function test_mutations_and_simulation_require_csrf(): void
    {
        $this->app->detectEnvironment(fn () => 'local');
        $this->post('/kupon')->assertStatus(419);
        $this->post('/simulasi', ['code' => 'HEMAT20'])->assertStatus(419);
        $this->assertDatabaseCount('coupons', 0);
    }
}
