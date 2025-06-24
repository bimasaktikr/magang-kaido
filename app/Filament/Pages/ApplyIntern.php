<?php

namespace App\Filament\Pages;

use App\Models\Department;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\University;
use App\Models\Education;
use App\Models\InternType;
use App\Models\Student;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class ApplyIntern extends Page implements HasForms
{
    // use WithFileUploads;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.apply-intern';

    protected static ?string $slug = 'apply-intern';


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

    public $introduction_letter_path = null;
    // public ?string $submission_letter_path = null;
    // public ?string $cv_path = null;

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
                                    // Set default when form is loaded
                                    return auth()->check() ? auth()->user()->name : null;
                                })
                                ->afterStateHydrated(function (TextInput $component, $state, $record) {
                                    // If we're editing an existing record with no name, and user is logged in
                                    if (empty($state) && auth()->check() && !$record) {
                                        $component->state(auth()->user()->name);
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
                                -> label('Tanggal Lahir')
                                ->required()
                                ->placeholder('Pilih tanggal lahir Anda'),
                            TextInput::make('phone')
                                    ->label('No. Telepon')
                                    ->tel()
                                    ->required()
                                    ->prefix('+62')
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
                                            // Ensure the slug is generated if empty
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
                                            // Ensure the slug is generated if empty
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
                                            // Ensure the slug is generated if empty
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
                            Grid::make(2)
                                ->schema([
                                    Select::make('year_of_admission')
                                        ->label('Tahun Masuk')
                                        ->required()
                                        ->options([
                                            '2020' => '2020',
                                            '2021' => '2021',
                                            '2022' => '2022',
                                            '2023' => '2023',
                                            '2024' => '2024',
                                        ]),
                                    Select::make('year_of_graduation')
                                        ->label('Tahun Keluar')
                                            ->required()
                                            ->options([
                                                '2024' => '2024',
                                                '2025' => '2025',
                                                '2026' => '2026',
                                                '2027' => '2027',
                                            ]),
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
                                    // Ensure the slug is generated if empty
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
                    Step::make('Upload Dokumen')
                        ->schema([
                            FileUpload::make('introduction_letter_path')
                                ->label('Surat Pengantar')
                                ->disk('public')
                                ->directory('uploads/introduction_letters')
                                ->preserveFilenames()
                                ->acceptedFileTypes(['application/pdf'])
                                ->maxSize(1024),
                            // FileUpload::make('submission_letter_path')
                            //     ->label('Surat Pengajuan')
                            //     ->disk('public')
                            //     ->directory('uploads/submission_letters')
                            //     ->preserveFilenames()
                            //     ->acceptedFileTypes(['application/pdf'])
                            //     ->maxSize(1024),
                            // FileUpload::make('cv_path')
                            //     ->label('Curriculum Vitae')
                            //     ->disk('public')
                            //     ->directory('uploads/cvs')
                            //     ->preserveFilenames()
                            //     ->acceptedFileTypes(['application/pdf'])
                            //     ->maxSize(1024),
                        ])
                ])
                ->submitAction(
                    Action::make('submit')
                        ->label('Submit')
                        ->submit(true) // this tells Filament to submit the form
                ),
            ]);
    }

    public function submit()
    {
        // Get the current state of the form
        $state = $this->form->getState();

        // Check if all required files are uploaded
        $requiredFileFields = ['introduction_letter_path', 'submission_letter_path', 'cv_path'];
        $missingFiles = [];

        foreach ($requiredFileFields as $field) {
            if (empty($state[$field])) {
                $missingFiles[] = $field;
            }
        }

        // If any required files are missing, show error and return
        if (!empty($missingFiles)) {
            $fileLabels = [
                'introduction_letter_path' => 'Surat Pengantar',
                'submission_letter_path' => 'Surat Pengajuan',
                'cv_path' => 'Curriculum Vitae',
            ];

            $missingLabels = array_map(function($field) use ($fileLabels) {
                return $fileLabels[$field];
            }, $missingFiles);

            Notification::make()
                ->title('Berkas tidak lengkap!')
                ->body('Silakan unggah semua berkas yang diperlukan: ' . implode(', ', $missingLabels))
                ->danger()
                ->send();

            return;
        }

        // Proceed with saving if all files are present
        DB::beginTransaction();

        try {
            $data = [
                'user_id' => auth()->user()->id,
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
                'year_of_admission' => $state['year_of_admission'] ?? null,
                'year_of_graduation' => $state['year_of_graduation'] ?? null,
                'introduction_letter_path' => $state['introduction_letter_path'],
                'submission_letter_path' => $state['submission_letter_path'],
                'cv_path' => $state['cv_path'],
            ];

            // Save student record
            // $student = Student::create($data);
            dd($data);
            // DB::commit();

            Notification::make()
                ->title('Data berhasil disimpan!')
                ->success()
                ->send();

            // Optional: redirect ke halaman lain
            // return redirect()->route('filament.pages.dashboard');

        } catch (\Throwable $th) {
            DB::rollBack();

            Notification::make()
                ->title('Terjadi kesalahan')
                ->body('Gagal menyimpan data: ' . $th->getMessage())
                ->danger()
                ->send();

            Log::error('Failed to save student data', [
                'exception' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && (
            auth()->user()->hasRole('Applicant') ||
            auth()->user()->hasRole('super_admin')
        ); // hide from sidebar
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Manipulasi data sebelum simpan, misal tambahkan user_id
        $data['user_id'] = auth()->id();

        return $data;
    }


}
