<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {
        Schema::create('zoom_calls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('zoom_meetings_id');
            $table->enum('status', ['active', 'ended'])->default('active');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('zoom_meetings_id')->references('id')->on('zoom_meetings')->onDelete('cascade');
        
            $table->unique(['user_id', 'zoom_meetings_id']);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('zoom_calls');
    }
};
