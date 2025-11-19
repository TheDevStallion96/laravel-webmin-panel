<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('deployments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->string('commit_hash');
            $table->string('branch');
            $table->string('status');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->text('log_path');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->index('site_id');
            $table->index('status');
            $table->index('started_at');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deployments');
    }
};
