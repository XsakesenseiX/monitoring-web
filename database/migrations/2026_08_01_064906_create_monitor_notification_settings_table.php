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
        Schema::create('monitor_notification_settings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('monitor_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('channel');
            $table->string('event');

            $table->boolean('enabled')
                ->default(true);

            $table->text('destination')
                ->nullable();

            $table->timestamps();

            $table->unique([
                'monitor_id',
                'channel',
                'event',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitor_notification_settings');
    }
};