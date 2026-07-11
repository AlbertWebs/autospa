<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_cards', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
        });

        Schema::table('job_cards', function (Blueprint $table) {
            $table->foreignId('vehicle_id')->nullable()->change();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('job_cards', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
        });

        Schema::table('job_cards', function (Blueprint $table) {
            $table->foreignId('vehicle_id')->nullable(false)->change();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->cascadeOnDelete();
        });
    }
};
