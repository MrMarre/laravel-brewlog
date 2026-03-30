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
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->string('brand_name');
            $table->string('product_name');
            $table->string('brew_method');
            $table->string('grind_size');
            $table->decimal('coffee_weight', 5, 1);
            $table->decimal('water_weight', 6, 1);
            $table->integer('bloom_time'); 
            $table->integer('brew_time');  
            $table->timestamps();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
