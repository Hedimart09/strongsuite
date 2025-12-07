<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GymSettings extends Model
{
    protected $fillable = [
        'gym_name',
        'logo',
        'email',
        'phone',
        'address',
        'timezone',
        'currency',
        'language',
        'tax_rate',
        'payment_gateways',
        'features',
    ];

    protected function casts(): array
    {
        return [
            'tax_rate' => 'decimal:2',
            'payment_gateways' => 'array',
            'features' => 'array',
        ];
    }

    public static function get(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'gym_name' => 'Strongsuite',
                'timezone' => 'Africa/Accra',
                'currency' => 'GHS',
                'language' => 'en',
                'tax_rate' => 15.00,
            ]
        );
    }
}
