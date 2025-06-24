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
        Schema::table('applications', function (Blueprint $table) {
            $table->string('introduction_letter_path')->nullable(); // untuk file pengantar
            $table->string('submission_letter_path')->nullable();   // untuk surat pengajuan
            $table->string('cv_path')->nullable();   // untuk surat pengajuan
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn(['introduction_letter_path', 'submission_letter_path', 'cv_path']);
        });
    }
};
