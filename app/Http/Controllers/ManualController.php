<?php

namespace App\Http\Controllers;

use App\Support\AttendanceSettings;
use App\Support\CommissionSettings;
use Illuminate\View\View;

class ManualController extends Controller
{
    public function index(): View
    {
        $sections = collect(config('manual.sections', []))
            ->map(function (array $section) {
                $title = $section['title'] ?? '';

                $section['topics'] = collect($section['topics'] ?? [])
                    ->filter(function (array $topic) use ($title) {
                        $heading = $topic['heading'] ?? '';

                        if ($title === 'Staff' && $heading === 'Attendance') {
                            return AttendanceSettings::enabled();
                        }

                        if ($title === 'Mission Control' && $heading === 'Commission stats') {
                            return CommissionSettings::enabled();
                        }

                        return true;
                    })
                    ->values()
                    ->all();

                return $section;
            })
            ->all();

        return view('manual.index', [
            'sections' => $sections,
        ]);
    }
}
