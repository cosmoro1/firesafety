<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Wrap the create logic in this check
        if (!Schema::hasTable('incident_histories')) {
            Schema::create('incident_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('incident_id')->constrained('incidents')->onDelete('cascade');
                $table->string('stage')->default('SIR');
                $table->string('title')->nullable();
                $table->string('type')->nullable();
                $table->string('location')->nullable();
                $table->string('incident_date')->nullable();
                $table->string('reported_by')->nullable();
                $table->text('description')->nullable();
                $table->json('images')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('incident_histories');
    }
};