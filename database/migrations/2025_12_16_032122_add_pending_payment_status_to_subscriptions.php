<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        // Add metadata column for storing additional subscription info
        if (! Schema::hasColumn('subscriptions', 'metadata')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->json('metadata')->nullable();
            });
        }

        // Handle database-specific constraint updates
        if ($driver === 'pgsql') {
            // For PostgreSQL, alter the check constraint to include new status
            Schema::table('subscriptions', function (Blueprint $table) {
                // Drop existing check constraint
                DB::statement('ALTER TABLE subscriptions DROP CONSTRAINT IF EXISTS subscriptions_status_check');

                // Recreate with new status
                DB::statement("ALTER TABLE subscriptions ADD CONSTRAINT subscriptions_status_check CHECK (status::text = ANY (ARRAY['pending_payment'::character varying, 'active'::character varying, 'expired'::character varying, 'cancelled'::character varying, 'suspended'::character varying]::text[]))");

                // Change default
                $table->string('status')->default('pending_payment')->change();
            });
        } else {
            // For SQLite, MySQL, etc., just change the default
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->string('status')->default('pending_payment')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        // Remove metadata column
        if (Schema::hasColumn('subscriptions', 'metadata')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->dropColumn('metadata');
            });
        }

        // Handle database-specific constraint updates
        if ($driver === 'pgsql') {
            // Revert to original check constraint
            Schema::table('subscriptions', function (Blueprint $table) {
                DB::statement('ALTER TABLE subscriptions DROP CONSTRAINT IF EXISTS subscriptions_status_check');
                DB::statement("ALTER TABLE subscriptions ADD CONSTRAINT subscriptions_status_check CHECK (status::text = ANY (ARRAY['active'::character varying, 'expired'::character varying, 'cancelled'::character varying, 'suspended'::character varying]::text[]))");

                $table->string('status')->default('active')->change();
            });
        } else {
            // For SQLite, MySQL, etc., just change the default back
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->string('status')->default('active')->change();
            });
        }
    }
};
