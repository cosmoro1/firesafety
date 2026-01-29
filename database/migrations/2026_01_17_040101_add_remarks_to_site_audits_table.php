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
    Schema::table('site_audits', function (Blueprint $table) {
        $table->text('remarks')->nullable(); // Stores the explanation
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_audits', function (Blueprint $table) {
            //
        });
    }
};
