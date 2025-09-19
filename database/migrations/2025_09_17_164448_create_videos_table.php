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
        Schema::create('videos', function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->string('title');
            $table->text('description');
            $table->foreignUuid('course_id')->constrained('courses')->onDelete('cascade');
            $table->string('gumlet_asset_id');
            $table->integer('video_order');
            $table->integer('duration_in_seconds')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('videos');
    }
};
