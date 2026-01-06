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
        Schema::create('monitored_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('path')->unique();
            $table->string('log_path')->nullable();
            $table->string('type')->default('unknown');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitored_projects');
    }
};
