<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('calendars', function (Blueprint $table) {
            $table->id();
            $table->year('year');
            $table->unsignedTinyInteger('month');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
        });
        \DB::table('calendars')->update(['user_id' => 1]);
        Schema::table('calendars', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }

    public function down(): void {
        Schema::dropIfExists('calendars');
    }
};
