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
        // Add only the new columns
        $table->string('stage')->default('SIR')->after('status'); 
        $table->text('admin_remarks')->nullable()->after('description');
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
