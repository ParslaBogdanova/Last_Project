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
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('color', 7)->nullable();
            $table->unsignedBigInteger('day_id');
            $table->foreign('day_id')->references('id')->on('days')->onDelete('cascade'); 
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->time('start_time');
            $table->time('end_time')->nullable();
            $table->timestamps();
        });

        \DB::table('schedules')->update(['user_id' => 1]);
        Schema::table('schedules', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
