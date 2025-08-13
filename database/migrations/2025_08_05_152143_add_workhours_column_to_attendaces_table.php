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
        Schema::table('attendances', function (Blueprint $table) {
            $table->time('workhours')->nullable();
            $table->enum('status', ['present', 'late', 'leave early', 'absent', 'sick', 'leave'])->default('absent'); // possible values: present, absent, leave, etc.
            $table->enum('work_location', ['office', 'home'])->default('office'); // default value is 'office'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['workhours', 'status', 'work_location']);
        });
    }
};
