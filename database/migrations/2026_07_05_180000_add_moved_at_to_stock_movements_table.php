<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->timestamp('moved_at')->nullable()->after('user_id');
            $table->index(['product_id', 'moved_at']);
        });

        DB::table('stock_movements')
            ->whereNull('moved_at')
            ->update(['moved_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'moved_at']);
            $table->dropColumn('moved_at');
        });
    }
};
