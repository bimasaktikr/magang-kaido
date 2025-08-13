<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\Student;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\CarbonPeriod;
use Illuminate\Validation\ValidationException;

class BulkWorkMode extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Internship';
    protected static ?string $navigationLabel = 'Set Status Kerja';
    protected static ?string $slug = 'set-status-kerja';

    protected static string $view = 'filament.pages.bulk-work-mode';


    // Form state
    public array $student_ids = [];
    public ?string $from = null;
    public ?string $to = null;
    public ?string $work_location = 'WFO';

    public static function shouldRegisterNavigation(): bool
    {
        // only show in navigation for superadmin and ketua tim
        if (!auth()->check()) {
            return false;
        }

        $user = auth()->user();
        if ($user->hasRole('superadmin') || $user->hasRole('ketua tim')) {
            return true;
        }

        return false;
    }


    public function mount(): void
    {
        $today = now()->toDateString();
        $this->from = $today;
        $this->to   = $today;
        $this->form->fill([
            'student_ids'   => $this->student_ids,
            'from'          => $this->from,
            'to'            => $this->to,
            'work_location' => $this->work_location,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([

                // 1) Students (active only), multi-select + searchable
                Select::make('student_ids')
                    ->label('Pilih Mahasiswa')
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->options(
                        Student::activeIntern()
                            ->orderBy('name')
                            ->pluck('name', 'id')
                    )
                    ->required()
                    ->hint('Bisa pilih banyak, bisa cari nama'),

                // 2) Date range (today or later)
                DatePicker::make('from')
                    ->label('Dari')
                    ->native(false)
                    ->minDate(today())          // only from today onwards
                    ->required()
                    ->reactive(),

                DatePicker::make('to')
                    ->label('Sampai')
                    ->native(false)
                    ->minDate(today())
                    ->required()
                    ->reactive()
                    ->helperText('Jika 1 hari, pilih tanggal yang sama untuk Dari & Sampai'),

                // 3) Work mode (single)
                Select::make('work_location')
                    ->label('Status')
                    ->options([
                        'WFO' => 'WFO (Office)',
                        'WFA' => 'WFA (Anywhere)',
                        // If later you want WFA: 'WFA' => 'WFA (Anywhere)',
                    ])
                    ->required()
                    ->native(false),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('apply')
                ->label('Terapkan')
                ->icon('heroicon-o-check-circle')
                ->requiresConfirmation()
                ->action(fn () => $this->apply()),
        ];
    }

    public function apply(): void
    {
        $data = $this->form->getState();

        if (empty($data['student_ids'])) {
            throw ValidationException::withMessages([
                'student_ids' => 'Pilih minimal satu mahasiswa.',
            ]);
        }

        $from = Carbon::parse($data['from'])->startOfDay();
        $to   = Carbon::parse($data['to'])->startOfDay();
        if ($from->gt($to)) {
            throw ValidationException::withMessages([
                'to' => 'Tanggal "Sampai" harus setelah atau sama dengan "Dari".',
            ]);
        }
        if ($from->lt(today())) {
            throw ValidationException::withMessages([
                'from' => 'Tanggal tidak boleh sebelum hari ini.',
            ]);
        }

        $dates = collect(CarbonPeriod::create($from, $to))
            ->map(fn (Carbon $d) => $d->toDateString());

        $workLocation = $data['work_location'];

        // Chunk to stay memory-friendly
        collect($data['student_ids'])->chunk(500)->each(function ($studentIds) use ($dates, $workLocation) {
            $rows = [];
            foreach ($studentIds as $sid) {
                foreach ($dates as $date) {
                    $rows[] = [
                        'student_id'    => $sid,
                        'date'          => $date,
                        'work_location' => $workLocation,
                    ];
                }
            }

            // Create if missing; update existing: ONLY work_location
            Attendance::upsert(
                $rows,
                ['student_id', 'date'],
                ['work_location']
            );
        });

        Notification::make()
            ->title('Berhasil')
            ->body('Work mode diterapkan untuk '.count($data['student_ids']).' mahasiswa pada rentang tanggal terpilih.')
            ->success()
            ->send();

        // Optional: reset selections
        // $this->student_ids = [];
        // $this->form->fill(['student_ids' => []]);
    }
}
