<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Member;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::query()
            ->with('member')
            ->latest('check_in_time');

        if ($request->filled('date')) {
            $query->whereDate('check_in_time', $request->input('date'));
        } else {
            $query->today();
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('member', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('member_id', 'like', "%{$search}%");
            });
        }

        $attendances = $query->paginate(50);

        return Inertia::render('Attendance/Index', [
            'attendances' => $attendances,
            'filters' => $request->only(['date', 'search']),
        ]);
    }

    public function scan()
    {
        return Inertia::render('Attendance/Scan');
    }

    public function checkInByQr(Request $request)
    {
        $request->validate([
            'qr_code' => ['required', 'string'],
        ]);

        $member = Member::where('qr_code', $request->input('qr_code'))->first();

        if (! $member) {
            return back()->with('error', 'Invalid QR code');
        }

        return $this->processCheckIn($member, 'qr_code');
    }

    public function manual()
    {
        $members = Member::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'member_id', 'photo']);

        return Inertia::render('Attendance/Manual', [
            'members' => $members,
        ]);
    }

    public function checkInManual(Request $request)
    {
        $request->validate([
            'member_id' => ['required', 'exists:members,id'],
        ]);

        $member = Member::findOrFail($request->input('member_id'));

        return $this->processCheckIn($member, 'manual');
    }

    protected function processCheckIn(Member $member, string $method)
    {
        $activeCheckIn = Attendance::where('member_id', $member->id)
            ->whereNull('check_out_time')
            ->first();

        if ($activeCheckIn) {
            $activeCheckIn->update([
                'check_out_time' => now(),
            ]);

            return redirect()->route('attendance.index')
                ->with('success', "{$member->name} checked out successfully");
        }

        Attendance::create([
            'member_id' => $member->id,
            'check_in_time' => now(),
            'check_in_method' => $method,
        ]);

        return redirect()->route('attendance.index')
            ->with('success', "{$member->name} checked in successfully");
    }

    public function checkOut(Attendance $attendance)
    {
        if ($attendance->check_out_time) {
            return redirect()->back()
                ->with('error', 'Already checked out');
        }

        $attendance->update([
            'check_out_time' => now(),
        ]);

        return redirect()->route('attendance.index')
            ->with('success', 'Checked out successfully');
    }
}
