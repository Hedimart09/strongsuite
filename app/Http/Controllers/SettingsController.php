<?php

namespace App\Http\Controllers;

use App\Models\GymSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = GymSettings::get();

        return Inertia::render('settings/Index', [
            'settings' => $settings,
            'timezones' => $this->getTimezones(),
            'currencies' => $this->getCurrencies(),
            'paymentGateways' => $this->getAvailablePaymentGateways(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'gym_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'timezone' => 'required|string|in:'.implode(',', array_keys($this->getTimezones())),
            'currency' => 'required|string|in:'.implode(',', array_keys($this->getCurrencies())),
            'tax_rate' => 'required|numeric|min:0|max:100',
            'payment_gateways' => 'nullable|array',
            'payment_gateways.*' => 'string|in:paystack,flutterwave,stripe,manual',
            'logo' => 'nullable|image|max:2048',
        ]);

        $settings = GymSettings::get();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($settings->logo && Storage::disk('public')->exists($settings->logo)) {
                Storage::disk('public')->delete($settings->logo);
            }

            $path = $request->file('logo')->store('logos', 'public');
            $validated['logo'] = $path;
        } else {
            // Remove logo from data if no new logo is uploaded
            unset($validated['logo']);
        }

        $settings->update($validated);

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully');
    }

    private function getTimezones(): array
    {
        return [
            'Africa/Accra' => 'Ghana (GMT)',
            'Africa/Lagos' => 'Nigeria (WAT)',
            'Africa/Nairobi' => 'Kenya (EAT)',
            'Africa/Cairo' => 'Egypt (EET)',
            'Africa/Johannesburg' => 'South Africa (SAST)',
            'Europe/London' => 'London (GMT)',
            'Europe/Paris' => 'Paris (CET)',
            'America/New_York' => 'New York (EST)',
            'America/Chicago' => 'Chicago (CST)',
            'America/Los_Angeles' => 'Los Angeles (PST)',
            'Asia/Dubai' => 'Dubai (GST)',
            'Asia/Kolkata' => 'India (IST)',
            'Asia/Singapore' => 'Singapore (SGT)',
            'Australia/Sydney' => 'Sydney (AEDT)',
        ];
    }

    private function getCurrencies(): array
    {
        return [
            'GHS' => 'Ghana Cedi (GH₵)',
            'NGN' => 'Nigerian Naira (₦)',
            'KES' => 'Kenyan Shilling (KSh)',
            'ZAR' => 'South African Rand (R)',
            'USD' => 'US Dollar ($)',
            'EUR' => 'Euro (€)',
            'GBP' => 'British Pound (£)',
            'AED' => 'UAE Dirham (د.إ)',
            'INR' => 'Indian Rupee (₹)',
        ];
    }

    private function getAvailablePaymentGateways(): array
    {
        return [
            [
                'id' => 'paystack',
                'name' => 'Paystack',
                'description' => 'Accept payments via cards and mobile money',
                'enabled' => config('services.paystack.public_key') !== null,
            ],
            [
                'id' => 'flutterwave',
                'name' => 'Flutterwave',
                'description' => 'Accept payments via cards and mobile money',
                'enabled' => config('services.flutterwave.public_key') !== null,
            ],
            [
                'id' => 'stripe',
                'name' => 'Stripe',
                'description' => 'Accept international card payments',
                'enabled' => config('services.stripe.key') !== null,
            ],
            [
                'id' => 'manual',
                'name' => 'Manual/Cash',
                'description' => 'Record cash and bank transfer payments manually',
                'enabled' => true,
            ],
        ];
    }
}
