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
        Schema::create('product_taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('country_code', 2);
            $table->string('unit')->nullable();
            $table->string('type');
            $table->decimal('rate', 8, 4)->nullable();
            $table->decimal('amount', 12, 4)->nullable();
            $table->string('currency', 3)->nullable();
            $table->timestamps();

            $table->index(['product_id', 'country_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_taxes');
    }
};
