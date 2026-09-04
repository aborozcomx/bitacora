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
            $table->foreignId('client_id')->nullable()->after('branch_id')->constrained()->nullOnDelete();
            $table->foreignId('client_branch_id')->nullable()->after('client_id')->constrained()->nullOnDelete();
            $table->string('folio_prefix')->nullable()->after('folio_number');
            $table->string('folio_consecutive')->nullable()->after('folio_prefix');
        });

        Schema::table('bitacora_employees', function (Blueprint $table) {
            $table->foreignId('bitacora_activity_id')->nullable()->after('bitacora_id')->constrained()->cascadeOnDelete();
        });

        Schema::table('bitacora_expenses', function (Blueprint $table) {
            $table->foreignId('bitacora_activity_id')->nullable()->after('bitacora_id')->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bitacora_expenses', function (Blueprint $table) {
            $table->dropForeign(['bitacora_activity_id']);
            $table->dropColumn('bitacora_activity_id');
        });

        Schema::table('bitacora_employees', function (Blueprint $table) {
            $table->dropForeign(['bitacora_activity_id']);
            $table->dropColumn('bitacora_activity_id');
        });

        Schema::table('bitacoras', function (Blueprint $table) {
            $table->dropForeign(['client_branch_id']);
            $table->dropForeign(['client_id']);
            $table->dropColumn(['client_id', 'client_branch_id', 'folio_prefix', 'folio_consecutive']);
        });
    }
};
