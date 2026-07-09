<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mpesa_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('flow', 30);
            $table->string('direction', 20);
            $table->string('reference')->nullable();
            $table->string('conversation_id')->nullable();
            $table->string('originator_conversation_id')->nullable();
            $table->string('checkout_request_id')->nullable();
            $table->string('merchant_request_id')->nullable();
            $table->string('phone', 30)->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('status', 30)->nullable();
            $table->string('result_code', 50)->nullable();
            $table->text('result_description')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['flow', 'direction']);
            $table->index('reference');
            $table->index('conversation_id');
            $table->index('originator_conversation_id');
            $table->index('checkout_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mpesa_transactions');
    }
};
