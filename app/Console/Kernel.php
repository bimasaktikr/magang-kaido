<?php

namespace App\Console;

use App\Models\Attendance;
use App\Models\Intern;
use App\Services\AttendanceService;
use App\Services\InternService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{   

    protected $commands = [
        \App\Console\Commands\ImportUniversities::class,
    ];

    protected function schedule(Schedule $schedule)
    {   
        
        $schedule->call(function () {
            $internService = app(InternService::class);
        
            $interns = $internService->getAllActiveInterns();

            foreach ($interns as $intern) {
                Attendance::create([
                    'intern_id' => $intern->id,
                    'date' => now()->toDateString(),
                ]);
            }
        })->weekdays()
            ->daily()
            ->at('5.00');
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
