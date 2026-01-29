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
    Schema::table('incident_histories', function (Blueprint $table) {
        // We use JSON type to store multiple image paths in one column
        $table->json('images')->nullable()->after('description');
    });
}

public function down()
{
    Schema::table('incident_histories', function (Blueprint $table) {
        $table->dropColumn('images');
    });
}
      
};
