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
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('translation_key_id');
            $table->unsignedBigInteger('locale_id');
            $table->text('value');
            $table->enum('status', ['draft', 'approved'])->default('approved');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->unique(['translation_key_id', 'locale_id'], 'uq_translation');
            $table->foreign('translation_key_id')->references('id')->on('translation_keys')->onDelete('cascade');
            $table->foreign('locale_id')->references('id')->on('locales')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
