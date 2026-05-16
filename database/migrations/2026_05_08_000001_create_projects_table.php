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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 36)->unique()->index();

            // Basic Info
            $table->string('name');
            $table->string('path')->unique();

            // Searchable Indices (Primary architectural choices)
            $table->json('blueprints')->nullable();
            $table->string('server')->nullable()->index();
            $table->string('database')->nullable()->index();
            $table->string('cache')->nullable()->index();
            $table->string('storage')->nullable()->index();
            $table->string('search')->nullable()->index();

            // The Master Record (Stores the full architectural DNA)
            $table->json('config')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
