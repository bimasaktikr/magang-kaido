<?php

namespace App\Services;

use App\Models\Student;
use Illuminate\Support\Facades\Auth;

class StudentService
{
    /**
     * Add a new student.
     *
     * @param array $data
     * @return Student
     */
    public function addStudent(array $data)
    {
        return Student::create([
            'user_id' => Auth::user()->id,
            'name' => $data['name'],
            'phone' => $data['phone'],
            'student_number' => $data['student_number'],
            'gender' => $data['gender'],
            'birth_date' => $data['birth_date'],
            'university_id' => $data['university_id'] ?? null,
            'faculty_id' => $data['faculty_id'] ?? null,
            'department_id' => $data['department_id'] ?? null,
            'program_id' => $data['program_id'] ?? null,
            'education_id' => $data['education_id'],
            'intern_type_id' => $data['intern_type_id'],
            'req_start_date' => $data['req_start_date'],
            'req_end_date' => $data['req_end_date'],
        ]);
    }



    /**
     * Get all students.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllStudent()
    {
        return Student::all();
    }

    /**
     * Get all active students, optionally filtered by division.
     *
     * @param int|null $divisionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllActiveStudents($divisionId = null)
    {
        $query = Student::where('work_status', 'accepted');

        if ($divisionId) {
            $query->where('division_id', $divisionId);
        }

        return $query->get();
    }

    /**
     * Get the authenticated student.
     *
     * @return Student|null
     */
    public function getAuthStudent()
    {
        $studentId = Auth::user()->student->id ?? null;

        if (!$studentId) {
            return null;
        }

        return Student::where('id', $studentId)->first();
    }

    /**
     * Get a selected student by ID.
     *
     * @param int $studentId
     * @return Student|null
     */
    public function getSelectedStudent($studentId)
    {
        return Student::where('id', $studentId)->first();
    }


    /**
     * Set the role of the student (by user_id or student_id) to 'Applicant'.
     *
     * @param int $studentId
     * @return bool
     */
    public function setRoleToApplicant($studentId)
    {
        $student = Student::find($studentId);

        if (!$student || !$student->user_id) {
            return false;
        }

        $user = \App\Models\User::find($student->user_id);

        if (!$user) {
            return false;
        }

        // Remove all roles and assign only 'Applicant'
        $user->syncRoles(['Applicant']);

        return true;
    }
}
