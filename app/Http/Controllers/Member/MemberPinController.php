<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\MemberPinToken;
use Inertia\Inertia;

class MemberPinController extends Controller
{
    public function show(string $token)
    {
        $pinToken = MemberPinToken::where('token', $token)
            ->with('member')
            ->first();

        if (! $pinToken) {
            return Inertia::render('Member/ViewPin', [
                'error' => 'Invalid or expired link. Please contact the gym for assistance.',
                'pin' => null,
                'member' => null,
            ]);
        }

        if ($pinToken->isExpired()) {
            return Inertia::render('Member/ViewPin', [
                'error' => 'This link has expired. Please contact the gym for a new PIN.',
                'pin' => null,
                'member' => null,
            ]);
        }

        // Mark as viewed (only once)
        if (! $pinToken->isViewed()) {
            $pinToken->markAsViewed();
        }

        return Inertia::render('Member/ViewPin', [
            'error' => null,
            'pin' => $pinToken->pin,
            'member' => $pinToken->member,
            'memberId' => $pinToken->member->member_id,
            'loginUrl' => route('member.login'),
        ]);
    }
}
