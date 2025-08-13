<?php

namespace App\Services;

use App\Models\Intern;
use Illuminate\Support\Facades\Auth;

class InternService
{
    public function getAllIntern()
    {
        // Fetch data from the database
        return Intern::all();
    }

    public function getAllActiveInterns($divisionId = null)
    {
        // Fetch data from the database
        $query = Intern::where('work_status', 'accepted');

        if ($divisionId) {
            $query->where('division_id', $divisionId);
        }

        return $query->get();
    }

    /**
     * Get Auth Intern
     */
    public function getAuthIntern()
    {
        $internId = Auth::user()->intern->id;

        // Fetch data from the database
        return Intern::where('id', $internId)->first();
        // return $internId;
    }

    /**
     * Get Auth Intern
     */
    public function getSelectedIntern($internId)
    {
        return Intern::where('id', $internId)->first();
    }
}
