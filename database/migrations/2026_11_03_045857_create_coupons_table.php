<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 32)->unique();
            $table->enum('discount_type', ['percentage', 'fixed']);
            $table->unsignedBigInteger('discount_value');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->unsignedInteger('max_uses');
            $table->unsignedInteger('used_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['is_active', 'starts_on', 'ends_on']);
        });
        DB::statement("CREATE TRIGGER coupon_limits_insert BEFORE INSERT ON coupons WHEN NEW.used_count < 0 OR NEW.used_count > NEW.max_uses OR NEW.max_uses < 1 BEGIN SELECT RAISE(ABORT, 'coupon_usage_limit'); END");
        DB::statement("CREATE TRIGGER coupon_limits_update BEFORE UPDATE ON coupons WHEN NEW.used_count < 0 OR NEW.used_count > NEW.max_uses OR NEW.max_uses < 1 BEGIN SELECT RAISE(ABORT, 'coupon_usage_limit'); END");
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
