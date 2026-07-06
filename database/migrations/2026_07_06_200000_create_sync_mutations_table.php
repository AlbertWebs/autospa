<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_mutations', function (Blueprint $table) {
            $table->uuid('client_mutation_id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->uuid('entity_uuid')->nullable();
            $table->json('result')->nullable();
            $table->timestamp('applied_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_mutations');
    }
};
