<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('incident_histories', function (Blueprint $table) {
        $table->id();
        $table->foreignId('incident_id')->constrained('incidents')->onDelete('cascade');
        $table->string('stage')->default('SIR'); // SIR, PIR, FIR
        $table->string('title')->nullable();
        $table->string('type')->nullable();
        $table->string('location')->nullable();
        $table->string('incident_date')->nullable();
        $table->string('reported_by')->nullable();
        $table->text('description')->nullable();
        $table->json('images')->nullable(); // Stores photo paths array
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_histories');
    }
};
