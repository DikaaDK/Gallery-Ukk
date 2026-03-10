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
        Schema::table('foto', function (Blueprint $table) {
            $table->dropForeign(['AlbumID']);
            $table->unsignedBigInteger('AlbumID')->nullable()->change();
            $table->foreign('AlbumID')->references('AlbumID')->on('album')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('foto', function (Blueprint $table) {
            $table->dropForeign(['AlbumID']);
            $table->unsignedBigInteger('AlbumID')->nullable(false)->change();
            $table->foreign('AlbumID')->references('AlbumID')->on('album')->cascadeOnDelete();
        });
    }
};
