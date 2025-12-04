<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // IMPORTANT: Do not touch the `role` column here to avoid duplicates.
        // `role` is managed by an earlier migration (0002_01_add_role_to_users_table.php).

        if (!Schema::hasColumn('users', 'is_active')) {
            Schema::table('users', function (Blueprint $table) {
                // Place is_active after role if role exists, otherwise at the end
                if (Schema::hasColumn('users', 'role')) {
                    $table->boolean('is_active')->default(true)->after('role');
                } else {
                    $table->boolean('is_active')->default(true);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Only drop is_active; do not drop role here as it is managed by another migration
        if (Schema::hasColumn('users', 'is_active')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
};
