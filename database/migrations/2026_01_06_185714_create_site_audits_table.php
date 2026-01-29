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
    Schema::create('site_audits', function (Blueprint $table) {
        $table->id();
        $table->string('barangay');
        $table->string('owner_name');
        $table->string('type'); // Residential, Commercial, etc.
        $table->string('address');
        $table->string('contact_person');
        $table->string('contact_number');
        
        // This stores the structural table (Wood/Cement/Metal)
        $table->json('structure_data')->nullable(); 
        
        // This stores the Yes/No answers for Sections A, B, C, D
        $table->json('checklist_data')->nullable(); 
        
        $table->text('hazards')->nullable(); // Identified Hazards
        $table->integer('compliance_score')->default(0); // 0-100%
        $table->string('risk_level')->default('Low'); // Low, Medium, High
        $table->string('auditor_id'); // User ID of who encoded it
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_audits');
    }
};
