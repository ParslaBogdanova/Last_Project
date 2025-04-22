<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('user_zoom_meetings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('zoom_meetings_id');
            $table->date('date');
            $table->string('status')->default('invited');
            $table->timestamps();
        
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('zoom_meetings_id')->references('id')->on('zoom_meetings')->onDelete('cascade');
        
            $table->unique(['user_id', 'zoom_meetings_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('user_zoom_meetings');
    }
};
