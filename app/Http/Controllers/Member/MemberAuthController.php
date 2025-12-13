<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class MemberAuthController extends Controller
{
    public function showLogin()
    {
        return Inertia::render('Member/Login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'member_id' => ['required', 'string'],
            'pin' => ['required', 'string', 'size:4'],
        ]);

        $key = 'member-login:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'member_id' => ['Too many login attempts. Please try again in '.RateLimiter::availableIn($key).' seconds.'],
            ]);
        }

        $member = Member::where('member_id', $request->member_id)->first();

        if (! $member || ! Hash::check($request->pin, $member->pin)) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'member_id' => ['Invalid member ID or PIN.'],
            ]);
        }

        if (! $member->isActive()) {
            throw ValidationException::withMessages([
                'member_id' => ['Your membership is inactive. Please contact the gym.'],
            ]);
        }

        RateLimiter::clear($key);

        auth('member')->login($member);

        $request->session()->regenerate();

        return redirect()->route('member.dashboard')
            ->with('success', 'Welcome back, '.$member->name.'!');
    }

    public function logout(Request $request)
    {
        auth('member')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('member.login')
            ->with('success', 'You have been logged out successfully.');
    }
}
