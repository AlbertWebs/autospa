<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type')->default('appointment');
            $table->string('status')->default('pending');
            $table->dateTime('scheduled_at');
            $table->dateTime('ends_at')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['branch_id', 'status', 'scheduled_at']);
        });

        Schema::create('booking_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('duration_minutes')->default(30);
            $table->timestamps();
        });

        Schema::create('recurring_booking_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('frequency');
            $table->unsignedTinyInteger('interval')->default(1);
            $table->date('ends_on')->nullable();
            $table->unsignedInteger('occurrences')->nullable();
            $table->json('days_of_week')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_booking_rules');
        Schema::dropIfExists('booking_services');
        Schema::dropIfExists('bookings');
    }
};
