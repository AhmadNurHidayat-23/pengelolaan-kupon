<?php

namespace Tests\Feature;

use PDO;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class CouponConcurrencyTest extends TestCase
{
    public function test_parallel_simulations_never_exceed_the_usage_limit(): void
    {
        $database = tempnam(sys_get_temp_dir(), 'coupon-test-');
        $environment = ['APP_ENV' => 'testing', 'DB_CONNECTION' => 'sqlite', 'DB_DATABASE' => $database,
            'DB_URL' => '', 'CACHE_STORE' => 'array', 'SESSION_DRIVER' => 'array'];
        $processes = [];
        try {
            (new Process([PHP_BINARY, 'artisan', 'migrate', '--force', '--no-interaction'], base_path(), $environment))->mustRun();
            $bootstrap = 'require "vendor/autoload.php"; $app = require "bootstrap/app.php"; '
                .'$app->make(\\Illuminate\\Contracts\\Console\\Kernel::class)->bootstrap(); '
                .'\\Illuminate\\Support\\Carbon::setTestNow("2026-11-03 10:00:00"); ';
            $seed = $bootstrap.'\\App\\Models\\Coupon::create(["code"=>"BERSAMA", "discount_type"=>"percentage", "discount_value"=>20, '
                .'"starts_on"=>"2026-11-03", "ends_on"=>"2026-11-03", "max_uses"=>5, "is_active"=>true]);';
            (new Process([PHP_BINARY, '-r', $seed], base_path(), $environment))->mustRun();
            $redeem = $bootstrap.'$result=$app->make(\\App\\Services\\CouponRedeemer::class)->redeem("BERSAMA"); echo $result["success"] ? "1" : "0";';
            foreach (range(1, 12) as $worker) {
                $process = new Process([PHP_BINARY, '-r', $redeem], base_path(), $environment, timeout: 30);
                $process->start();
                $processes[] = $process;
            }
            $results = [];
            foreach ($processes as $process) {
                $process->wait();
                $this->assertTrue($process->isSuccessful(), $process->getErrorOutput().$process->getOutput());
                $results[] = $process->getOutput();
            }
            $this->assertSame(5, count(array_filter($results, fn ($result) => $result === '1')));
            $this->assertSame(7, count(array_filter($results, fn ($result) => $result === '0')));
            $connection = new PDO('sqlite:'.$database);
            $this->assertSame(5, (int) $connection->query('SELECT used_count FROM coupons')->fetchColumn());
        } finally {
            foreach ($processes as $process) {
                if ($process->isRunning()) {
                    $process->stop();
                }
            }
            foreach ([$database, $database.'-wal', $database.'-shm', $database.'-journal'] as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }
    }
}
