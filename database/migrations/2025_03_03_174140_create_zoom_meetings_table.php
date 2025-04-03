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
        Schema::create('zoom_meetings', function (Blueprint $table) {
            $table->id();
            $table->string('title_zoom');
            $table->text('topic_zoom')->nullable();
            $table->time('start_time');
            $table->time('end_time')->nullable();

            $table->date('date');
            $table->unsignedBigInteger('creator_id');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
        \DB::table('zoom_meetings')->update(['creator_id' => 1]);
        Schema::table('zoom_meetings', function (Blueprint $table) {
            $table->unsignedBigInteger('creator_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zoom_meetings');
    }
};
