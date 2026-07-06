<?php

namespace App\Http\Controllers;

use App\Support\AttendanceSettings;
use Illuminate\View\View;

class ManualController extends Controller
{
    public function index(): View
    {
        $sections = collect(config('manual.sections', []))
            ->map(function (array $section) {
                if (($section['title'] ?? '') !== 'Staff') {
                    return $section;
                }

                $section['topics'] = collect($section['topics'] ?? [])
                    ->filter(fn (array $topic) => ($topic['heading'] ?? '') !== 'Attendance' || AttendanceSettings::enabled())
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
