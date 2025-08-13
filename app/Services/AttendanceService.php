<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Student;
use App\Services\InternService;
use Illuminate\Support\Carbon;

class AttendanceService
{
    protected $internService;

    public function __construct(InternService $internService)
    {
        $this->internService = $internService;
    }

    public function getAllAttendances()
    {
        // Fetch data from the database
        return Attendance::all();
    }

     /**
     * Attendance all active user on All Date Range
     */

    public function getAttendancesForActiveStudents()
    {
        $activeStudents = $this->internService->getAllActiveInterns(); // If you have a StudentService, use it here
        return Attendance::whereIn('student_id', $activeStudents->pluck('id'))->get();
    }

     /**
     * Attendance 1 user on Date Range
     */

    public function getAttendancesForDate($id, $date)
    {
        return Attendance::where('student_id', $id)
                    ->whereDate('date', $date)
                    ->first();
    }

    /**
     * Attendance all active user between Date Range
     */
    public function getAllAttendancesForDateRange($start_date, $end_date)
    {
        $activeStudents = $this->internService->getAllActiveInterns(); // If you have a StudentService, use it here

        return Attendance::whereIn('student_id', $activeStudents->pluck('id'))
        ->whereBetween('date', [Carbon::parse($start_date), Carbon::parse($end_date)])
        ->get();
    }

    /**
     * Attendance 1 user between Date Range
     */
    public function getAttendancesForDateRange($id, $start_date, $end_date)
    {
        $attendances = Attendance::where('student_id', $id)
                            ->whereBetween('date', [Carbon::parse($start_date), Carbon::parse($end_date)])
                            ->get();
        return $attendances;
    }


    /**
     * Make New Attendance Location for 1 user
     */
    public function makeAttendanceLocation($id, $date, $workLocation)
    {
        Attendance::updateOrCreate(
            [
                'student_id' => $id,
                'date' => Carbon::parse($date)->format('Y-m-d'),
            ],
            [
                'work_location' => $workLocation,
            ]
        );
    }

    /**
     * Make Status
     */
    public function getStatusAttendance($checkin = null, $checkout = null, $office = null)
    {
        // Default office times if no office provided
        $defaultStartTime = '07:30:00';
        $defaultEndTime = '15:00:00';

        if ($checkin) {
            $checkinTime = Carbon::parse($checkin);

            // Use office start_time if available, otherwise use default
            $startTime = $office && $office->start_time
                ? Carbon::parse($office->start_time)
                : Carbon::parse($defaultStartTime);

            if ($checkinTime->greaterThan($startTime)) {
                return 'late';
            } else {
                return 'present';
            }
        }

        if ($checkout) {
            $checkoutTime = Carbon::parse($checkout);

            // Use office end_time if available, otherwise use default
            $endTime = $office && $office->end_time
                ? Carbon::parse($office->end_time)
                : Carbon::parse($defaultEndTime);

            if ($endTime->greaterThan($checkoutTime)) {
                return 'early leave';
            } else {
                return 'present';
            }
        }
    }

    public function getFilteredAttendances($filters)
    {
        $query = Attendance::query();

        // Filter by student_id if provided
        if (isset($filters['student_id']) && $filters['student_id'] != '') {
            $query->where('student_id', $filters['student_id']);
        }
        // Filter by division if provided and student_id is not set
        elseif (isset($filters['division']) && $filters['division'] != '') {
            $studentIds = Student::where('division_id', $filters['division'])->pluck('id');
            $query->whereIn('student_id', $studentIds);
        }
        // Filter by month if provided
        if (isset($filters['month']) && $filters['month'] != '') {
            $query->whereMonth('date', $filters['month']);
        }

        return $query->get(); // Use get() to retrieve results for DataTables processing
    }

    public function markAttendance($studentId, $lat, $lng)
    {
        $today = now()->toDateString();
        $attendance = Attendance::firstOrCreate(
            [
                'student_id' => $studentId,
                'date' => $today,
            ]
        );

        if (!$attendance->check_in) {
            // First attendance: check-in
            $attendance->check_in = now()->format('H:i:s');
            $attendance->check_in_latitude = $lat;
            $attendance->check_in_longitude = $lng;
            // Set status after check-in
            $attendance->status = $this->getStatusAttendance($attendance->check_in, null);
            $attendance->save();
            return 'check_in';
        } else {
            // Second or later attendance: always update check-out
            $attendance->check_out = now()->format('H:i:s');
            $attendance->check_out_latitude = $lat;
            $attendance->check_out_longitude = $lng;
            // Set status after check-out
            $attendance->status = $this->getStatusAttendance($attendance->check_in, $attendance->check_out);
            // Calculate workhours if check_in and check_out are present
            if ($attendance->check_in && $attendance->check_out) {
                $checkIn = \Carbon\Carbon::createFromFormat('H:i:s', $attendance->check_in);
                $checkOut = \Carbon\Carbon::createFromFormat('H:i:s', $attendance->check_out);
                $diff = $checkIn->diff($checkOut);
                $attendance->workhours = $diff->format('%H:%I:%S');
            }
            $attendance->save();
            return 'check_out';
        }
    }

    public function getLast7Days($studentId)
    {
        return Attendance::where('student_id', $studentId)
            ->orderBy('date', 'desc')
            ->limit(7)
            ->get();
    }


}
