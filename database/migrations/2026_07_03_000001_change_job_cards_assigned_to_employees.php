<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_cards', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
        });

        DB::table('job_cards')->update(['assigned_to' => null]);

        Schema::table('job_cards', function (Blueprint $table) {
            $table->foreign('assigned_to')->references('id')->on('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('job_cards', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
        });

        DB::table('job_cards')->update(['assigned_to' => null]);

        Schema::table('job_cards', function (Blueprint $table) {
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
        });
    }
};
