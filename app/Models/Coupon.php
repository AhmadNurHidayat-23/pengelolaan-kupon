<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

#[Fillable(['code', 'discount_type', 'discount_value', 'starts_on', 'ends_on', 'max_uses', 'is_active'])]
class Coupon extends Model
{
    use HasFactory;

    public const STATUSES = ['valid' => 'Aktif', 'inactive' => 'Nonaktif', 'upcoming' => 'Belum berlaku', 'expired' => 'Kedaluwarsa', 'exhausted' => 'Habis'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'discount_value' => 'integer', 'max_uses' => 'integer', 'used_count' => 'integer'];
    }

    public function availability(?string $day = null): string
    {
        $day ??= now()->toDateString();

        return match (true) {
            ! $this->is_active => 'inactive',
            $day < $this->starts_on => 'upcoming',
            $day > $this->ends_on => 'expired',
            $this->used_count >= $this->max_uses => 'exhausted',
            default => 'valid',
        };
    }

    public function reason(): string
    {
        return match ($this->availability()) {
            'inactive' => 'Kupon dinonaktifkan oleh pengelola.',
            'upcoming' => 'Kupon belum berlaku. Mulai dapat digunakan pada '.Carbon::parse($this->starts_on)->format('d/m/Y').'.',
            'expired' => 'Masa berlaku kupon sudah berakhir.',
            'exhausted' => 'Batas penggunaan kupon sudah habis.',
            default => 'Kupon dapat digunakan untuk simulasi.',
        };
    }

    public function discountLabel(): string
    {
        return $this->discount_type === 'percentage'
            ? $this->discount_value.'%'
            : 'Rp'.number_format($this->discount_value, 0, ',', '.');
    }

    public function scopeWithAvailability(Builder $query, string $status): void
    {
        $day = now()->toDateString();
        if ($status === 'inactive') {
            $query->where('is_active', false);

            return;
        }
        $query->where('is_active', true);
        if ($status === 'upcoming') {
            $query->where('starts_on', '>', $day);
        } elseif ($status === 'expired') {
            $query->where('ends_on', '<', $day);
        } else {
            $query->where('starts_on', '<=', $day)->where('ends_on', '>=', $day)
                ->whereColumn('used_count', $status === 'exhausted' ? '>=' : '<', 'max_uses');
        }
    }
}
