<?php

// App/Filament/Pages/ApplicationDocuments.php
namespace App\Filament\Pages;

use App\Models\Application;
use App\Services\ApplicationService;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class ApplicationDocuments extends Page implements HasForms
{
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Internship';
    protected static ?string $title = 'Upload Dokumen Aplikasi';
    protected static string $view = 'filament.pages.application-documents';

    use InteractsWithForms;

    public ?int $applicationId = null;

    // IMPORTANT: array state for FileUpload fields
    public array $data = [];

    public function mount(ApplicationService $service): void
    {
        $app = $service->latestForUser((int) auth()->id());
        if (! $app) {
            Notification::make()->title('Anda belum membuat aplikasi.')->warning()->send();
            $this->redirectRoute('filament.pages.dashboard');
            return;
        }

        $this->applicationId = $app->id;

        // prefill
        $this->form->fill([
            'introduction_letter_path' => $app->introduction_letter_path,
            'submission_letter_path'   => $app->submission_letter_path,
            'cv_path'                  => $app->cv_path,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('introduction_letter_path')
                    ->label('Surat Pengantar')
                    ->disk('public')
                    ->directory('applications/letters')
                    ->acceptedFileTypes(['application/pdf','image/*'])
                    ->maxSize(5_000),

                FileUpload::make('submission_letter_path')
                    ->label('Surat Pengajuan')
                    ->disk('public')
                    ->directory('applications/submissions')
                    ->acceptedFileTypes(['application/pdf','image/*'])
                    ->maxSize(5_000),

                FileUpload::make('cv_path')
                    ->label('CV')
                    ->disk('public')
                    ->directory('applications/cv')
                    ->acceptedFileTypes(['application/pdf','image/*'])
                    ->maxSize(5_000),
            ])
            ->statePath('data'); // <-- binds to public array $data
    }

    public function save(): void
    {
        $state = $this->form->getState();

        $app = Application::find($this->applicationId);
        if (! $app || $app->student?->user_id !== auth()->id()) {
            Notification::make()->title('Tidak diizinkan.')->danger()->send();
            return;
        }

        $app->update([
            'introduction_letter_path' => $state['introduction_letter_path'] ?? null,
            'submission_letter_path'   => $state['submission_letter_path'] ?? null,
            'cv_path'                  => $state['cv_path'] ?? null,
        ]);

        Notification::make()->title('Dokumen disimpan.')->success()->send();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->hasRole('Applicant');
    }
}
