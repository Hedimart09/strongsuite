<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Inertia;

class MemberCheckInController extends Controller
{
    public function show()
    {
        $member = auth('member')->user();

        $activeCheckIn = Attendance::where('member_id', $member->id)
            ->whereNull('check_out_time')
            ->first();

        $recentAttendances = Attendance::where('member_id', $member->id)
            ->latest('check_in_time')
            ->limit(5)
            ->get();

        return Inertia::render('Member/CheckIn', [
            'activeCheckIn' => $activeCheckIn,
            'recentAttendances' => $recentAttendances,
        ]);
    }

    public function checkIn(Request $request)
    {
        $member = auth('member')->user();

        $key = 'member-checkin:'.$member->id;

        if (RateLimiter::tooManyAttempts($key, 10)) {
            return back()->with('error', 'Too many check-in attempts. Please wait a moment.');
        }

        RateLimiter::hit($key, 60);

        $activeCheckIn = Attendance::where('member_id', $member->id)
            ->whereNull('check_out_time')
            ->first();

        if ($activeCheckIn) {
            $activeCheckIn->update([
                'check_out_time' => now(),
            ]);

            return back()->with('success', 'Checked out successfully! Duration: '.$activeCheckIn->duration.' minutes');
        }

        Attendance::create([
            'member_id' => $member->id,
            'check_in_time' => now(),
            'check_in_method' => 'manual',
        ]);

        return back()->with('success', 'Checked in successfully! Enjoy your workout!');
    }
}
