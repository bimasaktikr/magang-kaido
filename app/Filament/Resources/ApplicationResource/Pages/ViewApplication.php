<?php

namespace App\Filament\Resources\ApplicationResource\Pages;

use App\Filament\Resources\ApplicationResource;
use App\Models\Application;
use App\Services\ApplicationService;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Carbon;
use Filament\Forms\Get;


class ViewApplication extends ViewRecord
{
    protected static string $resource = ApplicationResource::class;

    /** Convenience accessor */
    protected function service(): ApplicationService
    {
        return app(ApplicationService::class);
    }

    protected function getHeaderActions(): array
    {
        return [
            // 1) ACCEPT (Diterima)
            Actions\Action::make('accept')
                ->label('Terima (Diterima)')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (Application $record) => $record->status !== 'diterima')
                // Disable when docs incomplete
                ->disabled(fn (Application $record) => ! $this->service()->hasAllRequiredDocuments($record))
                ->modalDescription(fn (Application $record) => $this->service()->hasAllRequiredDocuments($record)
                    ? 'Status akan diubah menjadi DITERIMA dengan rentang tanggal sesuai permintaan.'
                    : 'Dokumen wajib belum lengkap. Lengkapi dulu sebelum menerima.')
                ->action(function (Application $record) {
                    // Business rule: accept = set accepted dates = requested dates, status=diterima
                    // (We can call a dedicated service method; here we reuse accept rules)
                    if (! $this->service()->hasAllRequiredDocuments($record)) {
                        Notification::make()->title('Dokumen belum lengkap.')->danger()->send();
                        return;
                    }

                    // If you want to centralize, add service->acceptAsAdmin($record) method.
                    $record->update([
                        'accepted_start_date' => $record->req_start_date,
                        'accepted_end_date'   => $record->req_end_date,
                        'status'              => 'diterima',
                        'hold_reason'         => null,
                        'rejection_reason'    => null,
                        'note'                => null,
                    ]);

                    Notification::make()
                        ->title('Lamaran diterima')
                        ->body('Tanggal diterima mengikuti tanggal permintaan.')
                        ->success()
                        ->send();

                    $this->record->refresh();
                }),

            // 2) REJECT (Ditolak) — requires reason
            Actions\Action::make('reject')
                ->label('Tolak (Ditolak)')
                ->color('danger')
                ->visible(fn (Application $record) => $record->status !== 'ditolak')
                ->form([
                    Textarea::make('rejection_reason')
                        ->label('Alasan Penolakan')
                        ->rows(5)
                        ->required()
                        ->placeholder('Tuliskan alasan penolakan...'),
                ])
                ->requiresConfirmation()
                ->action(function (array $data, Application $record) {
                    $reason = $data['rejection_reason'] ?? null;

                    // Prefer service method to keep rules in one place:
                    // $this->service()->rejectAsAdmin($record, $reason);
                    $record->update([
                        'status'            => 'ditolak',
                        'rejection_reason'  => $reason,
                        // keep note untouched; use note for internal remarks if you want
                    ]);

                    Notification::make()
                        ->title('Lamaran ditolak')
                        ->body('Alasan penolakan telah disimpan.')
                        ->danger()
                        ->send();

                    $this->record->refresh();
                }),

            // 3) HOLD / RESCHEDULE — admin picks new start; end auto by duration; optional hold_reason + note
            Actions\Action::make('reschedule')
                ->label('Ganti Tanggal (Hold)')
                ->color('warning')
                ->form(function (Application $record) {
                    $durationDays = Carbon::parse($record->req_start_date)
                        ->diffInDays(Carbon::parse($record->req_end_date)) + 1;

                    return [
                        Select::make('reason')
                            ->label('Alasan')
                            ->options([
                                'tanggal'                => 'Tanggal',
                                'dokumen_belum_lengkap'  => 'Dokumen belum lengkap',
                                'lainnya'                => 'Lainnya',
                            ])
                            ->required()
                            ->native(false),

                        // Show only when reason = 'tanggal'
                        DatePicker::make('new_start')
                            ->label('Tanggal Mulai Baru')
                            ->helperText("Durasi permintaan: {$durationDays} hari. Tanggal selesai akan dihitung otomatis.")
                            ->visible(fn (Get $get) => $get('reason') === 'tanggal')
                            ->required(fn (Get $get) => $get('reason') === 'tanggal'),

                        // Show only when reason != 'tanggal'
                        Textarea::make('hold_reason_text')
                            ->label('Alasan Hold')
                            ->rows(4)
                            ->placeholder('Tuliskan alasan hold…')
                            ->visible(fn (Get $get) => in_array($get('reason'), ['dokumen_belum_lengkap', 'lainnya'], true))
                            ->required(fn (Get $get) => in_array($get('reason'), ['dokumen_belum_lengkap', 'lainnya'], true)),

                        TextInput::make('note')
                            ->label('Catatan (opsional)'),
                    ];
                })
                ->action(function (array $data, Application $record) {
                    $reason = $data['reason'];

                    // Compute dates:
                    // - if reason = 'tanggal' → use new_start + duration
                    // - else → keep existing accepted dates if present, otherwise snap to requested dates
                    $reqStart = Carbon::parse($record->req_start_date);
                    $reqEnd   = Carbon::parse($record->req_end_date);
                    $duration = $reqStart->diffInDays($reqEnd) + 1;

                    if ($reason === 'tanggal') {
                        $newStart = Carbon::parse($data['new_start'])->startOfDay();
                        $newEnd   = (clone $newStart)->addDays($duration - 1);

                        $this->service()->putOnHoldWithDates(
                            app:        $record,
                            newStart:   $newStart,
                            newEnd:     $newEnd,
                            holdReason: 'tanggal',
                            note:       $data['note'] ?? null,
                        );

                        Notification::make()
                            ->title('Tanggal diubah (Hold)')
                            ->body("Jadwal baru: {$newStart->toDateString()} s.d. {$newEnd->toDateString()} (menunggu konfirmasi peserta).")
                            ->warning()
                            ->send();
                    } else {
                        // keep current accepted range if already set, otherwise default to requested
                        $currentStart = $record->accepted_start_date
                            ? Carbon::parse($record->accepted_start_date)
                            : $reqStart->clone();
                        $currentEnd = $record->accepted_end_date
                            ? Carbon::parse($record->accepted_end_date)
                            : $reqStart->clone()->addDays($duration - 1);

                        $this->service()->putOnHoldWithDates(
                            app:        $record,
                            newStart:   $currentStart,
                            newEnd:     $currentEnd,
                            holdReason: $reason, // 'dokumen_belum_lengkap' | 'lainnya'
                            note:       $data['hold_reason_text'] ?? ($data['note'] ?? null),
                        );

                        $reasonText = $reason === 'dokumen_belum_lengkap' ? 'Dokumen belum lengkap' : 'Lainnya';
                        Notification::make()
                            ->title('Status diubah ke HOLD')
                            ->body("Alasan: {$reasonText}" . (!empty($data['hold_reason_text']) ? " — {$data['hold_reason_text']}" : ''))
                            ->warning()
                            ->send();
                    }

                    $this->record->refresh();
                }),

        ];
    }
}
