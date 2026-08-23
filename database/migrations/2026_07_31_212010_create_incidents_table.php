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
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();

            $table->foreignId('monitor_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('status')->default('open');

            $table->timestamp('started_at');
            $table->timestamp('resolved_at')->nullable();

            $table->unsignedInteger('duration_seconds')->nullable();

            $table->string('error_message')->nullable();

            $table->unsignedSmallInteger('failure_count')->default(1);

            $table->timestamps();

            $table->index(['monitor_id', 'status']);
            $table->index(['monitor_id', 'started_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};