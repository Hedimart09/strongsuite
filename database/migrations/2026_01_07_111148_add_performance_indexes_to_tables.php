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
        // Add indexes to members table
        Schema::table('members', function (Blueprint $table) {
            $table->index('status');
            $table->index('name');
            $table->index('phone');
        });

        // Add indexes to subscriptions table
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->index('status');
            $table->index('end_date');
            $table->index(['status', 'end_date']);
        });

        // Add indexes to attendances table
        Schema::table('attendances', function (Blueprint $table) {
            $table->index('check_in_time');
            $table->index(['member_id', 'check_out_time']);
        });

        // Add indexes to payments table
        Schema::table('payments', function (Blueprint $table) {
            $table->index('status');
            $table->index('payment_date');
            $table->index(['status', 'payment_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['name']);
            $table->dropIndex(['phone']);
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['end_date']);
            $table->dropIndex(['status', 'end_date']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['check_in_time']);
            $table->dropIndex(['member_id', 'check_out_time']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['payment_date']);
            $table->dropIndex(['status', 'payment_date']);
        });
    }
};
