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
    Schema::create('trainings', function (Blueprint $table) {
        $table->id();
        $table->string('company_name');          // e.g. Jollibee - San Pedro
        $table->string('company_id');            // e.g. BIZ-2024-882
        $table->string('industry_type');         // Commercial or Industrial
        $table->string('representative_name');   // e.g. Ana Marie Doe
        $table->string('representative_position')->nullable(); 
        $table->string('topic');                 // e.g. Annual Fire Safety
        $table->date('date_conducted');
        $table->integer('attendees_count')->default(0);
        $table->string('status')->default('Scheduled'); // Issued, Pending, Scheduled
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainings');
    }
};
