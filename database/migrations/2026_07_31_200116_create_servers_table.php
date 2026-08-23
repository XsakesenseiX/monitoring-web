<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('host');
            $table->unsignedInteger('port')->default(22);

            $table->string('status')
                ->default('active');

            $table->string('environment')
                ->default('production');

            $table->timestamp('last_seen_at')->nullable();

            $table->timestamps();

            $table->unique(['project_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};