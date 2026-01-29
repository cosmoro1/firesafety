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
    Schema::create('documents', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->string('category'); // memo, policy, circular, sop, training, announcement
        $table->string('file_path');
        $table->string('file_type')->nullable(); // pdf, docx, etc.
        $table->string('file_size')->nullable();
        $table->text('description')->nullable();
        $table->unsignedBigInteger('uploaded_by');
        $table->integer('downloads')->default(0);
        $table->timestamps();

        $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('cascade');
    });
}
};
