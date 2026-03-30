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
        Schema::create('log_flavors', function (Blueprint $table) {
            
            $table->foreignId('log_id')->constrained('logs')->onDelete('cascade'); 
            $table->foreignId('flavor_id')->constrained('flavors')->onDelete('cascade'); 
            $table->enum('type', ['listed', 'tasted']);
            $table->timestamps();
            $table->primary(['log_id', 'flavor_id', 'type']);
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_flavors');
    }
};
