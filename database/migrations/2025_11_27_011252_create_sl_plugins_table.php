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
        Schema::create('scpsl_installed_plugins', function (Blueprint $table) {
            $table->id();
            $table->string('plugin_framework');
            $table->string('plugin_version');
            $table->string('plugin_id');
            $table->integer('server_id');
            $table->string('plugin_name');
            $table->string('plugin_icon')->nullable();
            $table->json('files');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scpsl_installed_plugins');
    }
};