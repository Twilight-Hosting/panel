<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('permission_manager_roles', function (Blueprint $table) {
            $table->json('excluded_servers')->default('[]');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permission_manager_roles', function (Blueprint $table) {
            $table->dropColumn('excluded_servers');
        });
    }
};
