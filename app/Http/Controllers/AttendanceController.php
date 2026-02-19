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
            ->where('date', $date)
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
                'photo_path' => $fileName,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'location_address' => $request->location_address,
            ]);
            return back()->with('success', 'Checked in successfully.');
        } else {
            if (!$attendance->check_out) {
                $attendance->update([
                    'check_out' => $time,
                    'work_hours' => now()->diffInMinutes($attendance->date->format('Y-m-d') . ' ' . $attendance->check_in),
                ]);
                return back()->with('success', 'Checked out successfully.');
            }
            return back()->with('error', 'You have already checked out for today.');
        }
    }

    public function reports(Request $request)
    {
        $query = Attendance::with('employee')->orderBy('date', 'desc');

        if ($request->has('date')) {
            $query->where('date', $request->date);
        }

        if ($request->has('month')) {
            $query->whereMonth('date', date('m', strtotime($request->month)))
                  ->whereYear('date', date('Y', strtotime($request->month)));
        }

        return Inertia::render('Attendance/Report', [
            'attendances' => $query->get(),
            'filters' => $request->only(['date', 'month']),
        ]);
    }
}
