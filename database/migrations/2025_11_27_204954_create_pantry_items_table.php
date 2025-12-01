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
        Schema::create('pantry_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('added_by')->nullable();
            $table->foreignId('ingredients_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->decimal('quantity', 10, 2)->default(0);
            $table->string('unit', 50)->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('location')->nullable(); //e.g. fridge, freezer, pantry...
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pantry_items');
    }
};
