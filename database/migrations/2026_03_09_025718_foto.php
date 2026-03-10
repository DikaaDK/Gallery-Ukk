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
        Schema::create('foto', function (Blueprint $table) {
            $table->id('FotoID');
            $table->string('JudulFoto', 255);
            $table->text('DeskripsiFoto');
            $table->date('TanggalUnggah');
            $table->string('LokasiFile', 255);
            $table->unsignedBigInteger('AlbumID');
            $table->unsignedBigInteger('UserID');
            $table->foreign('AlbumID')->references('AlbumID')->on('album')->cascadeOnDelete();
            $table->foreign('UserID')->references('UserID')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('foto', function (Blueprint $table) {
            $table->dropForeign(['AlbumID']);
            $table->dropForeign(['UserID']);
        });
        Schema::dropIfExists('foto');
    }
};
