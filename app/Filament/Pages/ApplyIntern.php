<?php

namespace App\Filament\Pages;

use App\Models\Application;
use App\Models\Department;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\University;
use App\Models\Education;
use App\Models\InternType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Actions\Action;
use App\Models\Student;
use Illuminate\Support\HtmlString;
use App\Services\StudentService;
use App\Services\ApplicationService;

class ApplyIntern extends Page implements HasForms
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.apply-intern';
    protected static ?string $slug = 'apply-intern';
    protected static ?string $navigationGroup = 'Internship';

    public string $name;
    public string $student_number;
    public string $gender;
    public string $phone;
    public ?string $birth_date = null;

    public ?int $university_id = null;
    public ?int $faculty_id = null;
    public ?int $department_id = null;
    public ?int $program_id = null;
    public ?int $education_id = null;
    public ?string $year_of_admission = null;
    public ?string $year_of_graduation = null;

    public ?int $intern_type_id = null;
    public ?string $req_start_date = null;
    public ?string $req_end_date = null;

    public function getTitle(): string|Htmlable
    {
        return 'Pendaftaran Magang';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Step::make('Data Mahasiswa')
                        ->description('Lengkapi Data Anda')
                        ->schema([
                            TextInput::make('name')
                                ->label('Nama Lengkap')
                                ->required()
                                ->maxLength(255)
                                ->placeholder('Masukkan nama lengkap Anda')
                                ->autocapitalize('words')
                                ->suffixIcon('heroicon-o-user')
                                ->autofocus()
                                ->dehydrateStateUsing(fn ($state) => ucwords(strtolower(trim($state))))
                                ->default(function() {
                                    return Auth::check() ? Auth::user()->name : null;
                                })
                                ->afterStateHydrated(function (TextInput $component, $state, $record) {
                                    if (empty($state) && Auth::check() && !$record) {
                                        $component->state(Auth::user()->name);
                                    }
                                }),
                            TextInput::make('student_number')
                                ->label('NIM')
                                ->required()
                                ->maxLength(255),
                            Select::make('gender')
                                ->label('Pilih Jenis Kelamin')
                                ->options([
                                    'male' => 'Laki-Laki',
                                    'female' => 'Perempuan',
                                ])
                                ->required(),
                            DatePicker::make('birth_date')
                                ->label('Tanggal Lahir')
                                ->required()
                                ->placeholder('Pilih tanggal lahir Anda'),
                            TextInput::make('phone')
                                ->label('No. Telepon')
                                ->tel()
                                ->required()
                                ->telRegex('/^(\+62|62|0)8[1-9][0-9]{6,10}$/')
                                ->maxLength(15)
                                ->helperText('Format: 08xxxxxxxxxx atau +628xxxxxxxxxx')
                                ->placeholder('81234567890')
                                ->formatStateUsing(fn ($state) => $state ? preg_replace('/^(\+62|62|0)/', '', $state) : null)
                                ->dehydrateStateUsing(fn ($state) => $state ? '+62' . preg_replace('/^(\+62|62|0)/', '', $state) : null),
                        ]),
                    Step::make('Data Universitas')
                        ->description('Masukan Data Universitas Anda')
                        ->schema([
                            Select::make('university_id')
                                ->label('Universitas')
                                ->required()
                                ->searchable()
                                ->reactive()
                                ->live()
                                ->options(University::all()->pluck('name', 'id'))
                                ->placeholder('Pilih universitas Anda')
                                ->createOptionForm([
                                    TextInput::make('name')
                                        ->label('Masukkan Nama Universitas')
                                        ->required()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            $set('slug', str($state)->slug());
                                        }),
                                    TextInput::make('slug')
                                        ->label('Slug')
                                        ->required()
                                        ->maxLength(255)
                                        ->helperText('Slug akan otomatis dibuat dari nama universitas')
                                        ->readonly(),
                                ])
                                ->createOptionUsing(function ($data) {
                                    if (empty($data['slug']) && !empty($data['name'])) {
                                        $data['slug'] = str($data['name'])->slug();
                                    }
                                    return University::create($data)->id;
                                }),
                            Grid::make(2)
                                ->schema([
                                    Select::make('faculty_id')
                                        ->label('Fakultas')
                                        ->required()
                                        ->searchable()
                                        ->reactive()
                                        ->live()
                                        ->options(Faculty::all()->pluck('name', 'id'))
                                        ->placeholder('Pilih Fakultas Anda')
                                        ->createOptionForm([
                                            TextInput::make('name')
                                                ->label('Masukkan Nama Fakultas')
                                                ->required()
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(function ($state, callable $set) {
                                                    $set('slug', str($state)->slug());
                                                }),
                                            TextInput::make('slug')
                                                ->label('Slug')
                                                ->required()
                                                ->maxLength(255)
                                                ->helperText('Slug akan otomatis dibuat dari nama universitas')
                                                ->disabled(),
                                        ])
                                        ->createOptionUsing(function ($data) {
                                            if (empty($data['slug']) && !empty($data['name'])) {
                                                $data['slug'] = str($data['name'])->slug();
                                            }
                                            return Faculty::create([
                                                'name' => $data['name'],
                                                'slug' => $data['slug'],
                                            ])->id;
                                        }),
                                    Select::make('department_id')
                                        ->label('Departemen')
                                        ->required()
                                        ->searchable()
                                        ->reactive()
                                        ->live()
                                        ->options(Department::all()->pluck('name', 'id'))
                                        ->placeholder('Pilih Departemen Anda')
                                        ->createOptionForm([
                                            TextInput::make('name')
                                                ->label('Masukkan Nama Departemen')
                                                ->required()
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(function ($state, callable $set) {
                                                    $set('slug', str($state)->slug());
                                                }),
                                            TextInput::make('slug')
                                                ->label('Slug')
                                                ->required()
                                                ->maxLength(255)
                                                ->helperText('Slug akan otomatis dibuat dari nama universitas')
                                                ->disabled(),
                                        ])
                                        ->createOptionUsing(function ($data) {
                                            if (empty($data['slug']) && !empty($data['name'])) {
                                                $data['slug'] = str($data['name'])->slug();
                                            }
                                            return Department::create([
                                                'name' => $data['name'],
                                                'slug' => $data['slug'],
                                            ])->id;
                                        }),
                                ]),
                            Grid::make(2)
                                ->schema([
                                    Select::make('program_id')
                                        ->label('Program Studi')
                                        ->required()
                                        ->searchable()
                                        ->reactive()
                                        ->live()
                                        ->options(Program::all()->pluck('name', 'id'))
                                        ->placeholder('Pilih Program Studi Anda')
                                        ->createOptionForm([
                                            TextInput::make('name')
                                                ->label('Masukkan Nama Program Studi')
                                                ->required()
                                                ->live(onBlur: true)
                                                ->afterStateUpdated(function ($state, callable $set) {
                                                    $set('slug', str($state)->slug());
                                                }),
                                            TextInput::make('slug')
                                                ->label('Slug')
                                                ->required()
                                                ->maxLength(255)
                                                ->helperText('Slug akan otomatis dibuat dari nama universitas')
                                                ->disabled(),
                                        ])
                                        ->createOptionUsing(function ($data) {
                                            if (empty($data['slug']) && !empty($data['name'])) {
                                                $data['slug'] = str($data['name'])->slug();
                                            }
                                            return Program::create([
                                                'name' => $data['name'],
                                                'slug' => $data['slug'],
                                            ])->id;
                                        }),
                                    Select::make('education_id')
                                        ->label('Jenjang Pendidikan')
                                        ->required()
                                        ->searchable()
                                        ->reactive()
                                        ->live()
                                        ->options(Education::all()->pluck('name', 'id'))
                                        ->placeholder('Pilih Jenjang Pendidikan Anda'),
                                ]),
                        ]),
                    Step::make('Data Magang')
                        ->description('Masukkan Data Detail Magang')
                        ->schema([
                            Select::make('intern_type_id')
                                ->label('Tipe Magang')
                                ->required()
                                ->searchable()
                                ->reactive()
                                ->live()
                                ->options(InternType::all()->pluck('name', 'id'))
                                ->placeholder('Pilih Tipe Magang Anda')
                                ->createOptionForm([
                                    TextInput::make('name')
                                        ->label('Masukkan Nama Tipe Magang')
                                        ->required()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            $set('slug', str($state)->slug());
                                        }),
                                ])
                                ->createOptionUsing(function ($data) {
                                    if (empty($data['slug']) && !empty($data['name'])) {
                                        $data['slug'] = str($data['name'])->slug();
                                    }
                                    return InternType::create([
                                        'name' => $data['name'],
                                        'slug' => $data['slug'],
                                    ])->id;
                                }),
                            Grid::make(2)
                                ->schema([
                                    DatePicker::make('req_start_date')
                                        ->label('Tanggal Mulai')
                                        ->required(),
                                    DatePicker::make('req_end_date')
                                        ->label('Tanggal Selesai')
                                        ->required(),
                                ]),
                        ]),
                ])->submitAction(
                    new HtmlString('<button type="submit" class="filament-button filament-button-size-md filament-button-color-primary">Simpan</button>')
                ),
            ]);
    }

    public function submit()
    {
        $state = $this->form->getState();

        try {
            $data = [
                'user_id' => Auth::user()->id,
                'name' => $state['name'],
                'phone' => $state['phone'],
                'student_number' => $state['student_number'],
                'gender' => $state['gender'],
                'birth_date' => $state['birth_date'],
                'university_id' => $state['university_id'] ?? null,
                'faculty_id' => $state['faculty_id'] ?? null,
                'department_id' => $state['department_id'] ?? null,
                'program_id' => $state['program_id'] ?? null,
                'education_id' => $state['education_id'],
                'intern_type_id' => $state['intern_type_id'],
                'req_start_date' => $state['req_start_date'],
                'req_end_date' => $state['req_end_date'],
            ];

            $studentService = new StudentService();
            $student = $studentService->addStudent($data);

            // set role to 'Applicant' for the student
            $studentService->setRoleToApplicant($student->id);

            // Prepare application data
            $applicationData = [
                'student_id' => $student->id,
                'intern_type_id' => $state['intern_type_id'],
                'req_start_date' => $state['req_start_date'],
                'req_end_date' => $state['req_end_date'],
                // Add other fields as needed
            ];

            $applicationService = new ApplicationService();
            $application = $applicationService->addApplication($applicationData);

            Notification::make()
                ->title('Data berhasil disimpan!')
                ->success()
                ->send();

            // Redirect to dashboard after successful submit
            return redirect()->route('filament.pages.dashboard');

        } catch (\Throwable $th) {
            Notification::make()
                ->title('Terjadi kesalahan')
                ->body('Gagal menyimpan data: ' . $th->getMessage())
                ->danger()
                ->send();

            Log::error('Failed to save student/application data', [
                'exception' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        if (!Auth::check()) return false;

        if (Auth::user()->hasRole('Applicant')) {
            $student = Student::where('user_id', Auth::id())->first();
            if (!$student) {
                return true;
            }
            $hasApplication = Application::where('student_id', $student->id)->exists();
            return !$hasApplication;
        }

        return false;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        return $data;
    }
}
