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
        Schema::create('bitacoras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('folio_number');
            $table->date('date');
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('bitacora_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bitacora_id')->constrained()->cascadeOnDelete();
            $table->foreignId('activity_type_id')->nullable()->constrained()->nullOnDelete();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('bitacora_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bitacora_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->decimal('hours_worked', 8, 2)->default(0.00);
            $table->decimal('overtime_hours', 8, 2)->default(0.00);
            $table->decimal('base_rate_applied', 10, 2)->default(0.00);
            $table->decimal('overtime_rate_applied', 10, 2)->default(0.00);
            $table->decimal('total_earned', 10, 2)->default(0.00);
            $table->timestamps();
        });

        Schema::create('bitacora_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bitacora_id')->constrained()->cascadeOnDelete();
            $table->string('concept');
            $table->decimal('amount', 10, 2);
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('card_type_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_card_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitacora_expenses');
        Schema::dropIfExists('bitacora_employees');
        Schema::dropIfExists('bitacora_activities');
        Schema::dropIfExists('bitacoras');
    }
};
