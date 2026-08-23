<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tenant_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('monitor_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('incident_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('channel');
            $table->string('event');

            $table->string('status')
                ->default('pending');

            $table->text('destination')
                ->nullable();

            $table->text('error_message')
                ->nullable();

            $table->timestamp('sent_at')
                ->nullable();

            $table->timestamps();

            $table->index([
                'monitor_id',
                'event',
            ]);

            $table->index([
                'status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};