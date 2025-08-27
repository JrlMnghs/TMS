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
        Schema::create('translation_key_tags', function (Blueprint $table) {
            $table->unsignedBigInteger('translation_key_id');
            $table->unsignedBigInteger('tag_id');
            $table->primary(['translation_key_id', 'tag_id']);
            $table->foreign('translation_key_id')->references('id')->on('translation_keys')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translation_key_tags');
    }
};


