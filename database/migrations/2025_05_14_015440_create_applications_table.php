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
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade')->nullable();
            $table->foreignId('intern_type_id')->constrained('intern_types')->onDelete('cascade')->nullable();
            $table->date('req_start_date')->nullable();
            $table->date('req_end_date')->nullable();
            $table->date('accepted_start_date')->nullable();
            $table->date('accepted_end_date')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.s
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
