<?php
// App/Services/ApplicationService.php
namespace App\Services;

use App\Models\Application;
use App\Models\Student;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class ApplicationService
{
    public function addApplication(array $data): Application
    {
        return DB::transaction(fn () => Application::create($data));
    }

    public function latestForUser(int $userId): ?Application
    {
        $student = Student::where('user_id', $userId)->first();
        if (! $student) return null;

        return Application::where('student_id', $student->id)->latest('id')->first();
    }

    /**
     * Accept application as admin: set accepted dates to requested dates, status to 'diterima'.
     */
    public function acceptAsAdmin(Application $application): void
    {
        // Only allow if not already accepted
        if ($application->status === 'diterima') {
            return;
        }

        // Set accepted dates to requested dates
        $application->accepted_start_date = $application->req_start_date;
        $application->accepted_end_date = $application->req_end_date;
        $application->status = 'diterima';
        $application->hold_reason = null;
        $application->rejection_reason = null;
        $application->save();

        // change role to intern using studentservice
        app(\App\Services\StudentService::class)->setRoleToIntern($application->student_id);
    }
    /**
     * Build a status payload for the widget/dashboard.
     */
    public function statusPayloadForUser(int $userId): ?array
    {
        $student = Student::where('user_id', $userId)->first();
        if (! $student) {
            return null;
        }

        $application = Application::where('student_id', $student->id)->first();
        if (! $application) {
            return null;
        }

        // detect missing documents
        $missingDocs = $this->missingRequiredDocuments($application);

        return [
            'id'                => $application->id,
            'status'            => $application->status,
            'accepted_start'    => $application->accepted_start_date,
            'accepted_end'      => $application->accepted_end_date,
            'hold_reason'       => $application->hold_reason ?? null,
            'rejection_reason'  => $application->rejection_reason ?? null,
            'missing_docs'      => $missingDocs,
        ];
    }

    public function missingRequiredDocuments(Application $app): array
    {
        $missing = [];
        if (! $app->introduction_letter_path) $missing[] = 'introduction_letter';
        if (! $app->submission_letter_path)   $missing[] = 'submission_letter';
        if (! $app->cv_path)                  $missing[] = 'cv';
        return $missing;
    }

    public function hasAllRequiredDocuments(Application $app): bool
    {
        return empty($this->missingRequiredDocuments($app));
    }

    public function canAccept(Application $app): bool
    {
        // Only allow accept from 'hold' or 'diproses' AND all docs complete
        return in_array($app->status, ['hold','diproses'], true)
            && $this->hasAllRequiredDocuments($app);
    }

    public function acceptForUser(int $applicationId, int $userId): Application
    {
        return DB::transaction(function () use ($applicationId, $userId) {
            $app = Application::lockForUpdate()->find($applicationId);
            if (! $app) throw new ModelNotFoundException('Application not found.');

            $this->assertOwnedByUser($app, $userId);

            if (! $this->canAccept($app)) {
                throw new \RuntimeException('Lengkapi dokumen sebelum menerima penawaran.');
            }

            $app->update([
                'status' => 'diterima',
                'rejection_reason' => null,
                'hold_reason' => null,
            ]);

             // change role to intern using studentservice
             app(\App\Services\StudentService::class)->setRoleToIntern($app->student_id);


            return $app->fresh();
        });
    }

    public function rejectForUser(int $applicationId, int $userId, string $reason): Application
    {
        return DB::transaction(function () use ($applicationId, $userId, $reason) {
            $app = Application::lockForUpdate()->find($applicationId);
            if (! $app) throw new ModelNotFoundException('Application not found.');

            $this->assertOwnedByUser($app, $userId);

            $app->update([
                'status' => 'ditolak',
                'rejection_reason' => $reason,
            ]);

            return $app->fresh();
        });
    }

    public function putOnHoldWithDates(
        Application $app,
        \Carbon\Carbon $newStart,
        \Carbon\Carbon $newEnd,
        ?string $holdReason = null,
        ?string $note = null
    ): Application {
        return DB::transaction(function () use ($app, $newStart, $newEnd, $holdReason, $note) {
            $app->update([
                'accepted_start_date' => $newStart->toDateString(),
                'accepted_end_date'   => $newEnd->toDateString(),
                'status'              => 'hold',
                'hold_reason'         => $holdReason,
                'note'                => $note,
            ]);
            return $app->fresh();
        });
    }

    protected function assertOwnedByUser(Application $app, int $userId): void
    {
        $student = $app->student; // needs belongsTo in Application model
        if (! $student || (int) $student->user_id !== (int) $userId) {
            throw new AuthorizationException('Tidak diizinkan.');
        }
    }
}
