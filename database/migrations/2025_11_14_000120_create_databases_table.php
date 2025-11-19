<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('databases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->enum('engine', ['mysql', 'pgsql']);
            $table->string('name');
            $table->string('username');
            $table->string('host');
            $table->unsignedInteger('port');
            $table->text('password_encrypted');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['site_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('databases');
    }
};
