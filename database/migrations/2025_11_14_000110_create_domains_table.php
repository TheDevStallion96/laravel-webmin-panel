<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->string('hostname')->unique();
            $table->boolean('is_primary')->default(false)->index();
            $table->boolean('https_forced')->default(false);
            $table->timestamp('created_at')->useCurrent();

            $table->index('site_id');
            $table->index('hostname');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
