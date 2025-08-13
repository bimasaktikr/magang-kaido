<?php

namespace App\Filament\Widgets;

use App\Models\Application;
use App\Services\ApplicationService;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ApplicationStatusWidget extends Widget
{
    protected static string $view = 'filament.widgets.application-status-widget';

    // Livewire state
    public ?int $rejectingApplicationId = null;
    public ?string $note = null;            // used as reject reason input
    public bool $showRejectForm = false;

    protected ApplicationService $service;

    public function mount(): void
    {
        $this->service = app(ApplicationService::class);
    }

    /** Payload for current user or null */
    public function getApplicationStatus(): ?array
    {
        $userId = (int) Auth::id();
        return $this->service->statusPayloadForUser($userId);
    }

    // ADD: helper to check document completeness
    public function documentsComplete(): bool
    {
        $payload = $this->getApplicationStatus();
        if (! $payload) return false;

        $app = Application::find($payload['id']);
        return $app ? $this->service->hasAllRequiredDocuments($app) : false;
    }

    /** Missing docs for current payload */
    public function missingDocs(): array
    {
        $payload = $this->getApplicationStatus();
        if (! $payload) return [];

        $app = Application::find($payload['id']);
        if (! $app) return [];

        return $this->service->missingRequiredDocuments($app);
    }

    /** Whether Accept is allowed */
    public function canAccept(): bool
    {
        $payload = $this->getApplicationStatus();
        if (! $payload) return false;

        $app = Application::find($payload['id']);
        return $app ? $this->service->canAccept($app) : false;
    }

    public function acceptApplication(int $applicationId): void
    {
        if (! $this->documentsComplete()) {
            Notification::make()
                ->title('Dokumen belum lengkap')
                ->body('Lengkapi semua dokumen sebelum menekan Terima.')
                ->danger()
                ->send();
            return;
        }

        try {
            $this->service->acceptForUser($applicationId, (int) Auth::id());

            Notification::make()->title('Aplikasi diterima.')->success()->send();

            $this->reset(['rejectingApplicationId', 'note', 'showRejectForm']);
            $this->dispatch('$refresh'); // Livewire v3
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal menerima aplikasi')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    // CHANGE: block opening reject form if docs incomplete (to disable "Tolak")
    public function openRejectForm(int $applicationId): void
    {
        if (! $this->documentsComplete()) {
            Notification::make()
                ->title('Dokumen belum lengkap')
                ->body('Lengkapi semua dokumen sebelum menekan Tolak.')
                ->danger()
                ->send();
            return;
        }

        $this->rejectingApplicationId = $applicationId;
        $this->note = null;
        $this->showRejectForm = true;
    }

    public function rejectApplication(): void
    {
        if (! $this->rejectingApplicationId) return;

        if (! $this->documentsComplete()) {
            Notification::make()
                ->title('Dokumen belum lengkap')
                ->body('Lengkapi semua dokumen sebelum menolak.')
                ->danger()
                ->send();
            return;
        }

        if (! filled($this->note)) {
            throw ValidationException::withMessages([
                'note' => 'Alasan penolakan wajib diisi.',
            ]);
        }

        try {
            $this->service->rejectForUser(
                $this->rejectingApplicationId,
                (int) Auth::id(),
                (string) $this->note
            );

            Notification::make()->title('Aplikasi ditolak.')->success()->send();

            $this->reset(['rejectingApplicationId', 'note', 'showRejectForm']);
            $this->dispatch('$refresh');
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Gagal menolak aplikasi')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public static function canView(): bool
    {
        return Auth::check() && Auth::user()->hasRole('Applicant');
    }
}
