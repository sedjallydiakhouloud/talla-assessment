<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('image_id')->nullable()->constrained()->onDelete('cascade'); 
            $table->string('api_image_id')->nullable(); // si l'image vient de l'API externe
            $table->timestamps();

            $table->unique(['user_id', 'image_id']); // éviter doublons pour uploads
            $table->unique(['user_id', 'api_image_id']); // éviter doublons pour API
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('favorites');
    }
};
