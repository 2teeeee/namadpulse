<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alert_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alert_rule_id')
                ->constrained('alert_rules')
                ->cascadeOnDelete();

            $table->decimal('price_at_trigger', 15, 2);
            $table->enum('channel', ['telegram', 'sms', 'web']);
            $table->enum('status', ['sent', 'failed'])->default('sent');
            $table->text('error_message')->nullable();

            $table->timestamp('sent_at');
            $table->timestamps();

            $table->index('alert_rule_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_logs');
    }
};
