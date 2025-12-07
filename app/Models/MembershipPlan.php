<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Formatter\IntlMoneyFormatter;
use Money\Money;

class MembershipPlan extends Model
{
    /** @use HasFactory<\Database\Factories\MembershipPlanFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'currency',
        'duration_in_days',
        'is_active',
        'features',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'duration_in_days' => 'integer',
            'is_active' => 'boolean',
            'features' => 'array',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price / 100, 2);
    }

    public function getMoney(): Money
    {
        return new Money($this->price, new Currency($this->currency));
    }

    public function getFormattedPrice(): string
    {
        $money = $this->getMoney();
        $currencies = new ISOCurrencies;
        $numberFormatter = new \NumberFormatter(config('app.locale', 'en_US'), \NumberFormatter::CURRENCY);
        $moneyFormatter = new IntlMoneyFormatter($numberFormatter, $currencies);

        return $moneyFormatter->format($money);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
