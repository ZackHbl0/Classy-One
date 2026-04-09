<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Planning;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $student = $request->user();

        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);

        // Determine week range for the selected month
        $firstDayOfMonthCarbon = Carbon::createFromDate($year, $month, 1);
        $firstWeek = $firstDayOfMonthCarbon->isoWeek;
        
        $lastDayOfMonthCarbon = $firstDayOfMonthCarbon->copy()->endOfMonth();
        $lastWeek = $lastDayOfMonthCarbon->isoWeek;

        // Handle end of year ISO week wrap
        if ($month == 1 && $firstWeek > 50) {
            $firstWeek = 1;
        }

        $records = Planning::where('idStudent', $student->idStudent)
            ->where(function($query) use ($month, $year, $firstWeek, $lastWeek) {
                $query->where(function($sub) use ($month, $year) {
                    $sub->whereMonth('date', $month)
                        ->whereYear('date', $year);
                })
                ->orWhere(function($sub) use ($firstWeek, $lastWeek) {
                    $sub->whereNull('date')
                        ->whereBetween('weekNumber', [$firstWeek, $lastWeek]);
                });
            })
            ->orderBy('weekNumber', 'desc')
            ->orderBy('date', 'desc')
            ->orderBy('check_in', 'asc')
            ->get();

        $summary = ["early_leave" => 0, "absents" => 0, "late_in" => 0, "leaves" => 0];
        $weeks = [];

        foreach ($records as $row) {
            // Stats calculations
            if (!empty($row->status)) {
                $status = trim($row->status);
                if ($status == 'Early Leave') $summary['early_leave']++;
                else if ($status == 'Absents') $summary['absents']++;
                else if ($status == 'Late in') $summary['late_in']++;
                else if ($status == 'Leaves') $summary['leaves']++;
            }

            // Week grouping
            $weekNum = $row->weekNumber;
            if (empty($weekNum) && !empty($row->date)) {
                $weekNum = (int) Carbon::parse($row->date)->isoWeek;
            }
            $weekKey = "Semaine " . ($weekNum ?? "??");

            if (!isset($weeks[$weekKey])) {
                $weeks[$weekKey] = [
                    "title" => $weekKey,
                    "weekNumber" => $weekNum,
                    "stats" => ["Absent" => 0, "Leave" => 0, "Late" => 0],
                    "items" => []
                ];
            }

            // Item type detection
            if (!empty($row->fileUrl) && (empty($row->date) || empty($row->check_in))) {
                $weeks[$weekKey]['items'][] = [
                    "type" => "schedule",
                    "label" => "Emploi du temps - " . $weekKey,
                    "fileUrl" => $row->fileUrl
                ];
                continue;
            }

            if (!empty($row->date)) {
                $status = trim($row->status ?? '');
                if ($status == 'Absents') $weeks[$weekKey]['stats']['Absent']++;
                else if ($status == 'Leaves') $weeks[$weekKey]['stats']['Leave']++;
                else if ($status == 'Late in') $weeks[$weekKey]['stats']['Late']++;

                $checkInDate = $row->check_in ? Carbon::parse($row->check_in) : null;
                $checkOutDate = $row->check_out ? Carbon::parse($row->check_out) : null;
                
                $parsedDate = Carbon::parse($row->date);

                $weeks[$weekKey]['items'][] = [
                    "type" => "attendance",
                    "id" => $row->id,
                    "date" => $parsedDate->format('d'),
                    "dayName" => $parsedDate->format('D'),
                    "checkIn" => $checkInDate ? $checkInDate->format('h:i A') : "--:--",
                    "checkOut" => $checkOutDate ? $checkOutDate->format('h:i A') : "--:--",
                    "totalHours" => "0", // Omitted in legacy migration schema update but Flutter doesn't crash on "0"
                    "status" => $status,
                    "fileUrl" => $row->fileUrl
                ];
            }
        }

        return response()->json([
            "success" => true,
            "data" => [
                "summary" => $summary,
                "weeks" => array_values($weeks)
            ]
        ]);
    }
}
