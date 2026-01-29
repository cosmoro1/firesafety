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
       Schema::create('incident_histories', function (Blueprint $table) {
        $table->id();
        $table->foreignId('incident_id')->constrained()->onDelete('cascade');
        $table->string('stage'); // SIR, PIR, or FIR
        $table->text('description'); // The report text at that stage
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
