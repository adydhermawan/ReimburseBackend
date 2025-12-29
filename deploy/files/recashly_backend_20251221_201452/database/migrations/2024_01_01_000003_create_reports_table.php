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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->integer('entry_count')->default(0);
            $table->enum('status', ['draft', 'generated', 'submitted', 'paid'])->default('draft');
            $table->string('pdf_path')->nullable();
            $table->date('payment_date')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['period_start', 'period_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
