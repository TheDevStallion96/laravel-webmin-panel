<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ssl_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->enum('type', ['letsencrypt', 'custom', 'self_signed']);
            $table->string('common_name');
            $table->timestamp('expires_at');
            $table->string('path_cert');
            $table->string('path_key');
            $table->timestamp('last_renewed_at')->nullable();
            $table->string('status');
            $table->timestamp('created_at')->useCurrent();

            $table->index('site_id');
            $table->index('expires_at');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ssl_certificates');
    }
};
