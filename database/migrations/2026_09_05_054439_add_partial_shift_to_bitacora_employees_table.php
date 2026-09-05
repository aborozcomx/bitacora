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
        Schema::table('bitacora_employees', function (Blueprint $table) {
            $table->boolean('is_partial_shift')->default(false)->after('is_absent');
            $table->string('partial_shift_reason')->nullable()->after('is_partial_shift');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bitacora_employees', function (Blueprint $table) {
            $table->dropColumn(['partial_shift_reason', 'is_partial_shift']);
        });
    }
};
