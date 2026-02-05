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
    Schema::table('trainings', function (Blueprint $table) {
        $table->string('representative_email')->after('representative_name')->nullable();
    });
}

public function down(): void
{
    Schema::table('trainings', function (Blueprint $table) {
        $table->dropColumn('representative_email');
    });
}
};
