<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
            ALTER TABLE sessions
            ALTER COLUMN last_activity TYPE integer
            USING EXTRACT(EPOCH FROM last_activity)::integer
        SQL);
    }

    public function down(): void
    {
        DB::statement(<<<'SQL'
            ALTER TABLE sessions
            ALTER COLUMN last_activity TYPE timestamp
            USING to_timestamp(last_activity)::timestamp
        SQL);
    }
};