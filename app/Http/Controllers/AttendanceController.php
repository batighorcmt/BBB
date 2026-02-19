<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $employee = Employee::where('user_id', $user->id)->first();
        
        $todayAttendance = null;
        if ($employee) {
            $todayAttendance = Attendance::where('employee_id', $employee->id)
                ->where('date', now()->format('Y-m-d'))
                ->first();
        }

        return Inertia::render('Attendance/Index', [
            'todayAttendance' => $todayAttendance,
            'employee' => $employee,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'location_address' => 'nullable|string',
        ]);

        $user = $request->user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return back()->with('error', 'Employee record not found.');
        }

        $date = now()->format('Y-m-d');
        $time = now()->format('H:i:s');

        $attendance = Attendance::where('employee_id', $employee->id)
            ->whereDate('date', $date)
            ->first();

        // Handle Image
        $img = $request->input('image');
        $img = str_replace('data:image/jpeg;base64,', '', $img);
        $img = str_replace(' ', '+', $img);
        $fileName = 'attendance/' . $employee->employee_code . '_' . time() . '.jpg';
        Storage::disk('public')->put($fileName, base64_decode($img));

        if (!$attendance) {
            Attendance::create([
                'employee_id' => $employee->id,
                'date' => $date,
                'check_in' => $time,
                'status' => 'present',
                'check_in_photo' => $fileName,
                'check_in_latitude' => $request->latitude,
                'check_in_longitude' => $request->longitude,
                'check_in_address' => $request->location_address,
            ]);
            return back()->with('success', 'Checked in successfully.');
        } else {
            if (!$attendance->check_out) {
                // Calculate work hours
                $checkInDateTime = \Carbon\Carbon::parse($attendance->date->format('Y-m-d') . ' ' . $attendance->check_in);
                $checkOutDateTime = now();
                $workHours = abs($checkOutDateTime->diffInMinutes($checkInDateTime));

                $attendance->update([
                    'check_out' => $time,
                    'work_hours' => $workHours,
                    'check_out_photo' => $fileName,
                    'check_out_latitude' => $request->latitude,
                    'check_out_longitude' => $request->longitude,
                    'check_out_address' => $request->location_address,
                ]);
                return back()->with('success', 'Checked out successfully.');
            }
            return back()->with('error', 'You have already checked out for today.');
        }
    }

    public function reports(Request $request)
    {
        $query = Attendance::with('employee')->orderBy('date', 'desc');

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        if ($request->filled('month')) {
            $date = \Carbon\Carbon::parse($request->month);
            $query->whereMonth('date', $date->month)
                  ->whereYear('date', $date->year);
        }

        $attendances = $query->get();

        // If monthly view, prepare matrix data
        $matrix = [];
        if ($request->filled('month')) {
            $date = \Carbon\Carbon::parse($request->month);
            $daysInMonth = $date->daysInMonth;
            $employees = Employee::where('deleted_at', null)->get();
            
            foreach ($employees as $emp) {
                $empAttendance = [];
                for ($i = 1; $i <= $daysInMonth; $i++) {
                    $d = $date->copy()->day($i)->format('Y-m-d');
                    $att = $attendances->where('employee_id', $emp->id)->where('date', $d)->first();
                    $empAttendance[$i] = $att ? [
                        'status' => 'P',
                        'check_in' => $att->check_in,
                        'check_out' => $att->check_out,
                    ] : ['status' => 'A'];
                }
                $matrix[] = [
                    'employee' => $emp,
                    'days' => $empAttendance
                ];
            }
        }

        return Inertia::render('Attendance/Report', [
            'attendances' => $attendances,
            'matrix' => $matrix,
            'daysInMonth' => $request->filled('month') ? \Carbon\Carbon::parse($request->month)->daysInMonth : 0,
            'filters' => $request->only(['date', 'month']),
        ]);
    }


}
