<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\Student;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Contracts\View\View;
use App\Services\AttendanceService;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;

class AttendancePage extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $view = 'filament.pages.attendance-page';

    protected static ?string $navigationGroup = 'Internship';

    protected static ?string $slug = 'presensi';

    protected static ?string $navigationLabel = 'Presensi';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $title = 'Presensi';

    public $checkInTime;
    public $checkOutTime;
    public $checkInLocation;
    public $checkOutLocation;
    public $buttonLabel = 'Check In';
    public $nextAction = 'check_in';
    public $last7Days = [];
    public $officeLat = 0;
    public $officeLng = 0;
    public $latitude;
    public $longitude;

    protected AttendanceService $attendanceService;

    public function mount()
    {
        $this->attendanceService = app(AttendanceService::class);

        $student = Student::where('user_id', Auth::id())->first();
        if (!$student) {
            $this->checkInTime = null;
            $this->checkOutTime = null;
            $this->checkInLocation = null;
            $this->checkOutLocation = null;
            $this->last7Days = [];
            // fetch office location even if no student
            $office = \App\Models\Office::first();
            $this->officeLat = $office?->latitude ?? 0;
            $this->officeLng = $office?->longitude ?? 0;
            return;
        }

        $today = now()->toDateString();
        $attendance = Attendance::where('student_id', $student->id)
            ->where('date', $today)
            ->first();

        if ($attendance) {
            $this->checkInTime = $attendance->check_in;
            $this->checkOutTime = $attendance->check_out;
            $this->checkInLocation = $attendance->check_in_latitude && $attendance->check_in_longitude
                ? $attendance->check_in_latitude . ', ' . $attendance->check_in_longitude
                : null;
            $this->checkOutLocation = $attendance->check_out_latitude && $attendance->check_out_longitude
                ? $attendance->check_out_latitude . ', ' . $attendance->check_out_longitude
                : null;
            if (!$attendance->check_in) {
                $this->buttonLabel = 'Check In';
                $this->nextAction = 'check_in';
            } elseif (!$attendance->check_out) {
                $this->buttonLabel = 'Check Out';
                $this->nextAction = 'check_out';
            } else {
                $this->buttonLabel = 'Attendance Complete';
                $this->nextAction = 'none';
            }
        } else {
            $this->buttonLabel = 'Check In';
            $this->nextAction = 'check_in';
        }

        // Use the service for last 7 days
        $this->last7Days = $this->attendanceService->getLast7Days($student->id);

        // fetch office location
        $office = \App\Models\Office::first();
        $this->officeLat = $office?->latitude ?? 0;
        $this->officeLng = $office?->longitude ?? 0;
    }

    public function submitAttendance()
    {
        $student = Student::where('user_id', Auth::id())->first();
        if (!$student) return;

        $this->attendanceService = app(AttendanceService::class);
        $result = $this->attendanceService->markAttendance(
            $student->id,
            $this->latitude,
            $this->longitude
        );
        // Optionally, set a success message or update UI state based on $result
        $this->mount(); // Refresh state
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Attendance::query()
                    ->whereHas('student', function ($query) {
                        $query->where('user_id', Auth::id());
                    })
            )
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('date')
                    ->sortable(),
                Tables\Columns\TextColumn::make('check_in')
                    ->label('checkInTime')
                    ->sortable(),
                Tables\Columns\TextColumn::make('check_out')
                    ->label('checkOutTime')
                    ->sortable(),
                Tables\Columns\TextColumn::make('check_in_location')
                    ->label('checkInLocation')
                    ->getStateUsing(function ($record) {
                        return "{$record->check_in_latitude}, {$record->check_in_longitude}";
                    })
                    ->sortable() // optional
                    ->searchable(), // optional
                Tables\Columns\TextColumn::make('check_out_location')
                    ->label('checkOutLocation')
                    ->getStateUsing(function ($record) {
                        return "{$record->check_out_latitude}, {$record->check_out_longitude}";
                    })
                    ->sortable() // optional
                    ->searchable(), //,
                TextColumn::make('work_location')
                    ->label('Status WFH'),
                TextColumn::make('status')
                    ->label('Status Presensi')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'present' => 'success',
                        'absent'  => 'danger',
                        'late'    => 'warning',
                        default   => 'gray',
                    })
            ])
            ->defaultSort('date', 'desc');
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return Auth::check() && $user && method_exists($user, 'hasRole') && (
            $user->hasRole('Intern') || $user->hasRole('super_admin')
        );
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = Auth::user();
        return Auth::check() && $user && method_exists($user, 'hasRole') && (
            $user->hasRole('Intern') || $user->hasRole('super_admin')
        );
    }
}
