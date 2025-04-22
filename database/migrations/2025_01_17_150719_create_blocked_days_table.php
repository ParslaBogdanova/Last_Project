<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('blocked_days', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('calendar_id');
            $table->date('date');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('reason')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    
        \DB::table('blocked_days')->update(['user_id' => 1]);
    
        Schema::table('blocked_days', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });
    }
    
    public function down(): void {
        Schema::dropIfExists('blocked_days');
    }
};
