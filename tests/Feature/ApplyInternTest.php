<?php

use App\Models\User;
use App\Models\University;
use App\Models\Faculty;
use App\Models\Department;
use App\Models\Program;
use App\Models\Education;
use App\Models\InternType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('can submit the ApplyIntern form with valid data', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $this->actingAs($user);

    $university = University::factory()->create();
    $faculty = Faculty::create(['name' => 'Test Faculty']);
    $department = Department::create(['name' => 'Test Department']);
    $program = Program::create(['name' => 'Test Program']);
    $education = Education::create(['name' => 'S1']);
    $internType = InternType::create(['name' => 'Magang Industri']);

    $data = [
        'name' => 'Test User',
        'student_number' => '123456789',
        'gender' => 'male',
        'phone' => '+6281234567890',
        'birth_date' => '2000-01-01',
        'university_id' => $university->id,
        'faculty_id' => $faculty->id,
        'department_id' => $department->id,
        'program_id' => $program->id,
        'education_id' => $education->id,
        'year_of_admission' => '2020',
        'year_of_graduation' => '2024',
        'intern_type_id' => $internType->id,
        'req_start_date' => '2024-07-01',
        'req_end_date' => '2024-12-31',
        'introduction_letter_path' => UploadedFile::fake()->create('introduction.pdf', 100, 'application/pdf'),
        'submission_letter_path' => UploadedFile::fake()->create('submission.pdf', 100, 'application/pdf'),
        'cv_path' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
    ];

    // You may need to adjust the route/endpoint depending on your Filament setup
    $response = $this->post('/apply-intern', $data);

    // If you still have dd($data) in the controller, expect a 500 or see the output
    $response->assertStatus(200)->assertSee('Test User');
    // If you remove dd($data), assert redirect or database state as needed
    // $response->assertRedirect();
    // $this->assertDatabaseHas('students', ['name' => 'Test User']);
});
