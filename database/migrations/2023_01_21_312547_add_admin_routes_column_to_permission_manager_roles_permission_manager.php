<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('permission_manager_roles', function (Blueprint $table) {
            $table->json('admin_routes')->nullable()->after('permissions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permission_manager_roles', function (Blueprint $table) {
            $table->dropColumn('admin_routes');
        });
    }
};
