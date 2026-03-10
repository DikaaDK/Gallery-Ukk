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
        Schema::create('likefoto', function (Blueprint $table) {
            $table->id('LikeID');
            $table->unsignedBigInteger('FotoID');
            $table->unsignedBigInteger('UserID');
            $table->date('TanggalLike');
            $table->foreign('FotoID')->references('FotoID')->on('foto')->cascadeOnDelete();
            $table->foreign('UserID')->references('UserID')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('likefoto', function (Blueprint $table) {
            $table->dropForeign(['FotoID']);
            $table->dropForeign(['UserID']);
        });
        Schema::dropIfExists('likefoto');
    }
};
