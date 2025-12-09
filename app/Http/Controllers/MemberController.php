<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\Member;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $query = Member::query()
            ->with(['subscriptions' => function ($q) {
                $q->latest()->limit(1);
            }]);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('member_id', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $members = $query->latest()->paginate(15);

        return Inertia::render('Members/Index', [
            'members' => $members,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Members/Create');
    }

    public function store(StoreMemberRequest $request)
    {
        $data = $request->validated();

        $data['member_id'] = $this->generateMemberId();
        $data['qr_code'] = $this->generateQrCode();
        $data['status'] = 'active';

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('members/photos', 'public');
        }

        $member = Member::create($data);

        return redirect()->route('members.show', $member)
            ->with('success', 'Member registered successfully');
    }

    public function show(Member $member)
    {
        $member->load([
            'subscriptions.membershipPlan',
            'payments' => fn ($q) => $q->latest()->limit(10),
            'attendances' => fn ($q) => $q->latest()->limit(10),
        ]);

        return Inertia::render('Members/Show', [
            'member' => $member,
        ]);
    }

    public function edit(Member $member)
    {
        return Inertia::render('Members/Edit', [
            'member' => array_merge($member->toArray(), [
                'date_of_birth' => $member->date_of_birth?->format('Y-m-d'),
            ]),
        ]);
    }

    public function update(UpdateMemberRequest $request, Member $member)
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            if ($member->photo) {
                Storage::disk('public')->delete($member->photo);
            }
            $data['photo'] = $request->file('photo')->store('members/photos', 'public');
        } else {
            // Remove photo from data if no new photo is uploaded
            unset($data['photo']);
        }

        $member->update($data);

        return redirect()->route('members.show', $member)
            ->with('success', 'Member updated successfully');
    }

    public function destroy(Member $member)
    {
        if ($member->photo) {
            Storage::disk('public')->delete($member->photo);
        }

        $member->delete();

        return redirect()->route('members.index')
            ->with('success', 'Member deleted successfully');
    }

    public function qrCode(Member $member)
    {
        $qrData = json_encode([
            'member_id' => $member->member_id,
            'qr_code' => $member->qr_code,
            'name' => $member->name,
        ]);

        $renderer = new ImageRenderer(
            new RendererStyle(400),
            new SvgImageBackEnd
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($qrData);

        return Inertia::render('Members/QrCode', [
            'member' => $member,
            'qrCodeSvg' => $qrCodeSvg,
        ]);
    }

    protected function generateMemberId(): string
    {
        do {
            $memberId = 'MEM-'.strtoupper(substr(uniqid().bin2hex(random_bytes(2)), 0, 8));
        } while (Member::where('member_id', $memberId)->exists());

        return $memberId;
    }

    protected function generateQrCode(): string
    {
        do {
            $qrCode = 'QR-'.strtoupper(substr(bin2hex(random_bytes(6)), 0, 12));
        } while (Member::where('qr_code', $qrCode)->exists());

        return $qrCode;
    }
}
