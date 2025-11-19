<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('root_path');
            $table->string('public_dir');
            $table->string('php_version');
            $table->string('repo_url')->nullable();
            $table->string('default_branch');
            $table->enum('status', ['active', 'paused', 'error'])->index();
            $table->json('environment');
            $table->enum('deploy_strategy', ['basic', 'zero_downtime']);
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->index('slug');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
