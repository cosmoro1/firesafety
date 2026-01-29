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
        $table->string('title')->nullable()->after('stage');
        $table->string('type')->nullable()->after('title');
        $table->string('location')->nullable()->after('type');
        $table->dateTime('incident_date')->nullable()->after('location');
        $table->string('reported_by')->nullable()->after('incident_date');
    });
}

public function down()
{
    Schema::table('incident_histories', function (Blueprint $table) {
        $table->dropColumn(['title', 'type', 'location', 'incident_date', 'reported_by']);
    });
}
};
