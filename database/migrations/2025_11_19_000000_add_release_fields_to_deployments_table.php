<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('deployments', function (Blueprint $table) {
            $table->string('release_name')->nullable()->after('branch');
            $table->text('release_path')->nullable()->after('release_name');
            $table->index('release_name');
        });
    }

    public function down(): void
    {
        Schema::table('deployments', function (Blueprint $table) {
            $table->dropIndex(['release_name']);
            $table->dropColumn(['release_name', 'release_path']);
        });
    }
};
