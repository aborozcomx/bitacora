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
        Schema::table('bitacoras', function (Blueprint $table) {
            $table->date('date')->nullable()->change();
        });

        Schema::table('bitacora_activities', function (Blueprint $table) {
            $table->date('date')->nullable()->after('activity_type_id');
        });

        Schema::table('bitacora_employees', function (Blueprint $table) {
            $table->date('date')->nullable()->after('employee_id');
        });

        Schema::table('bitacora_expenses', function (Blueprint $table) {
            $table->date('date')->nullable()->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bitacora_expenses', function (Blueprint $table) {
            $table->dropColumn('date');
        });

        Schema::table('bitacora_employees', function (Blueprint $table) {
            $table->dropColumn('date');
        });

        Schema::table('bitacora_activities', function (Blueprint $table) {
            $table->dropColumn('date');
        });

        Schema::table('bitacoras', function (Blueprint $table) {
            $table->date('date')->nullable(false)->change();
        });
    }
};
