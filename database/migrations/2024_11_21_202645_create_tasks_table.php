<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('completed')->default(false);
            $table->timestamps();
        });

        \DB::table('tasks')->update(['user_id' => 1]);
    }
    
    public function down(): void {
        Schema::dropIfExists('tasks');
    }
};
