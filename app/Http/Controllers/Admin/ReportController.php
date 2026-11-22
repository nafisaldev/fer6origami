<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $type = $request->query('type');
        $search = $request->string('search')->toString();

        $reports = Report::query()
            ->with(['user', 'resolver'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($search, function ($q) use ($search) {
                $q->where('description', 'like', '%' . $search . '%')
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', '%' . $search . '%'));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.reports.index', [
            'reports' => $reports,
            'selectedStatus' => $status,
            'selectedType' => $type,
            'search' => $search,
            'types' => Report::TYPES,
            'statuses' => Report::STATUSES,
        ]);
    }

    public function show(Report $report): View
    {
        $report->load(['user', 'resolver']);

        return view('admin.reports.show', [
            'report' => $report,
            'types' => Report::TYPES,
            'statuses' => Report::STATUSES,
        ]);
    }

    public function update(Request $request, Report $report): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', array_keys(Report::STATUSES))],
            'admin_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $resolvedAt = in_array($data['status'], ['resolved', 'rejected']) ? now() : null;
        $resolvedBy = in_array($data['status'], ['resolved', 'rejected']) ? Auth::id() : null;

        $report->update([
            'status' => $data['status'],
            'admin_notes' => $data['admin_notes'],
            'resolved_at' => $resolvedAt,
            'resolved_by' => $resolvedBy,
        ]);

        ActivityLogger::log('Report', 'status_updated', "Memperbarui status laporan ID: {$report->id} menjadi {$data['status']}", [
            'report_id' => $report->id,
            'status' => $data['status'],
            'admin_notes' => $data['admin_notes'],
        ]);

        return redirect()->route('admin.reports.index')->with('success', 'Laporan berhasil diperbarui.');
    }

    public function destroy(Report $report): RedirectResponse
    {
        $id = $report->id;
        $report->delete();

        ActivityLogger::log('Report', 'deleted', "Menghapus laporan ID: {$id}", ['report_id' => $id]);

        return redirect()->route('admin.reports.index')->with('success', 'Laporan berhasil dihapus.');
    }
}
