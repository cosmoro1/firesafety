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
    Schema::create('incidents', function (Blueprint $table) {
        $table->id();
        $table->string('type');        // SIR, PIR, FIR, etc.
        $table->string('title');       // Incident Title
        $table->string('location');    // Barangay
        $table->dateTime('incident_date'); // Combined Date & Time
        $table->text('description');
        $table->string('status')->default('Pending'); // Default status
        $table->string('reported_by')->nullable(); // Officer Name or ID
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
