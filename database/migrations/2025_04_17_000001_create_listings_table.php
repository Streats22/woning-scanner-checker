<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();

            $table->text('raw_input');

            $table->integer('price')->nullable();
            $table->string('currency', 10)->nullable();

            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();

            $table->text('description')->nullable();
            $table->string('contact')->nullable();

            $table->unsignedTinyInteger('scam_score')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }
};
