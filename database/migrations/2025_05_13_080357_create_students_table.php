<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('student_number')->unique()->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('phone')->nullable();
            $table->foreignId('university_id')->constrained('universities')->onDelete('cascade')->nullable();
            $table->foreignId('faculty_id')->constrained('faculties')->onDelete('cascade')->nullable();
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade')->nullable();
            $table->foreignId('program_id')->constrained('programs')->onDelete('cascade')->nullable();
            $table->string('education_id')->constrained('educations')->onDelete('cascade')->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->year('year_of_admission')->nullable();
            $table->year('year_of_graduation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
