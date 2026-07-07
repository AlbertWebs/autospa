<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')
            ->where('group', 'commission')
            ->where('key', 'trigger')
            ->where('value', 'pos_checkout')
            ->update(['value' => 'job_completed']);
    }

    public function down(): void
    {
        DB::table('settings')
            ->where('group', 'commission')
            ->where('key', 'trigger')
            ->where('value', 'job_completed')
            ->update(['value' => 'pos_checkout']);
    }
};
