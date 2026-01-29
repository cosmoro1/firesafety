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
    Schema::table('incidents', function (Blueprint $table) {
        // Adding the alarm_level column, defaulting to 'Low' so old records don't break
        $table->string('alarm_level')->default('Low')->after('location'); 
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            //
        });
    }
};
