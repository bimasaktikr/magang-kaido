<?php

// database/migrations/2025_08_13_000001_add_rejection_reason_to_applications.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->text('hold_reason')->nullable()->after('status');
            $table->text('rejection_reason')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn('hold_reason', 'rejection_reason');
        });
    }
};

