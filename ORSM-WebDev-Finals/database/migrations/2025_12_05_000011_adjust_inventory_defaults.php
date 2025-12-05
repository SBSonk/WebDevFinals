<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only run adjustments if the inventory table exists
        if (! Schema::hasTable('inventory')) {
            return;
        }

        // Ensure max_stock_level and last_restocked have safe defaults to prevent 1364 errors
        // Use raw SQL to avoid requiring doctrine/dbal for column modifications
        try {
            if (Schema::hasColumn('inventory', 'max_stock_level')) {
                // Set a sensible default if none exists
                DB::statement("ALTER TABLE `inventory` MODIFY `max_stock_level` INT NOT NULL DEFAULT 100");
            }
        } catch (\Throwable $e) {
            // ignore if the platform syntax differs; better to leave as-is than fail migration
        }

        try {
            if (Schema::hasColumn('inventory', 'last_restocked')) {
                // Allow NULL to avoid insert errors when not provided
                // Prefer default CURRENT_TIMESTAMP if supported
                try {
                    DB::statement("ALTER TABLE `inventory` MODIFY `last_restocked` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP");
                } catch (\Throwable $e) {
                    // Fallback: just make it nullable without default
                    DB::statement("ALTER TABLE `inventory` MODIFY `last_restocked` TIMESTAMP NULL");
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We won't revert defaults to avoid breaking existing data; this is a no-op.
    }
};
