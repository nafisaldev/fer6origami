<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\VoucherRequest;
use App\Models\UserMembership;
use App\Models\Voucher;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VoucherController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('managePlans', UserMembership::class);

        $search = $request->string('search')->toString();

        $vouchers = Voucher::query()
            ->when($search, fn ($query) => $query->where('code', 'like', '%' . $search . '%')
                ->orWhere('name', 'like', '%' . $search . '%'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.vouchers.index', [
            'vouchers' => $vouchers,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        $this->authorize('managePlans', UserMembership::class);

        return view('admin.vouchers.create', [
            'voucher' => new Voucher(['is_active' => true, 'type' => 'percent', 'per_user_limit' => 1]),
        ]);
    }

    public function store(VoucherRequest $request): RedirectResponse
    {
        $this->authorize('managePlans', UserMembership::class);

        $data = $this->payload($request);
        $data['created_by'] = auth()->id();

        $voucher = Voucher::create($data);

        ActivityLogger::log('Membership', 'voucher_created', "Menambahkan voucher {$voucher->code}", ['voucher_id' => $voucher->id]);

        return redirect()->route('admin.vouchers.index')->with('success', 'Voucher berhasil ditambahkan.');
    }

    public function edit(Voucher $voucher): View
    {
        $this->authorize('managePlans', UserMembership::class);

        return view('admin.vouchers.edit', ['voucher' => $voucher]);
    }

    public function update(VoucherRequest $request, Voucher $voucher): RedirectResponse
    {
        $this->authorize('managePlans', UserMembership::class);

        $voucher->update($this->payload($request));

        ActivityLogger::log('Membership', 'voucher_updated', "Memperbarui voucher {$voucher->code}", ['voucher_id' => $voucher->id]);

        return redirect()->route('admin.vouchers.index')->with('success', 'Voucher berhasil diperbarui.');
    }

    public function destroy(Voucher $voucher): RedirectResponse
    {
        $this->authorize('managePlans', UserMembership::class);

        $code = $voucher->code;
        $voucher->delete();

        ActivityLogger::log('Membership', 'voucher_deleted', "Menghapus voucher {$code}");

        return redirect()->route('admin.vouchers.index')->with('success', 'Voucher dihapus.');
    }

    private function payload(Request $request): array
    {
        $data = $request->validated();

        return [
            'code' => $data['code'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'value' => $data['value'],
            'max_discount' => filled($data['max_discount'] ?? null) ? $data['max_discount'] : null,
            'min_purchase' => filled($data['min_purchase'] ?? null) ? $data['min_purchase'] : null,
            'usage_limit' => filled($data['usage_limit'] ?? null) ? $data['usage_limit'] : null,
            'per_user_limit' => $data['per_user_limit'],
            'applies_to_plans' => ! empty($data['applies_to_plans']) ? array_values($data['applies_to_plans']) : null,
            'applies_to_durations' => ! empty($data['applies_to_durations']) ? array_values(array_map('intval', $data['applies_to_durations'])) : null,
            'valid_from' => $data['valid_from'] ?? null,
            'valid_until' => $data['valid_until'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
