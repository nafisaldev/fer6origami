<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anime;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(): View
    {
        $days = Anime::DAYS;

        $schedules = [];
        foreach (array_keys($days) as $day) {
            $schedules[$day] = Anime::where('release_day', $day)
                ->where('status', 'ongoing')
                ->orderBy('title')
                ->get();
        }

        $unscheduled = Anime::whereNull('release_day')
            ->where('status', 'ongoing')
            ->orderBy('title')
            ->get();

        return view('admin.schedules.index', [
            'days' => $days,
            'schedules' => $schedules,
            'unscheduled' => $unscheduled,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'anime_id' => ['required', 'exists:animes,id'],
            'release_day' => ['nullable', 'string', 'in:' . implode(',', array_keys(Anime::DAYS))],
        ]);

        $anime = Anime::findOrFail($request->input('anime_id'));
        $oldDay = $anime->release_day;
        $newDay = $request->input('release_day');

        $anime->update(['release_day' => $newDay]);

        ActivityLogger::log('Schedule', 'updated', "Mengubah jadwal rilis anime {$anime->title} dari '{$oldDay}' ke '{$newDay}'", [
            'anime_id' => $anime->id,
            'old_day' => $oldDay,
            'new_day' => $newDay,
        ]);

        return back()->with('success', "Jadwal rilis untuk {$anime->title} berhasil diperbarui.");
    }
}
