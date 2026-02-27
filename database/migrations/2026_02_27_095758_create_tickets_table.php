<?php

use App\Enums\TicketAiStatus;
use App\Enums\TicketStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('description');

            $table->string('status')->default(TicketStatus::OPEN->value);

            $table->string('category')->nullable();
            $table->string('sentiment')->nullable();
            $table->string('urgency')->nullable();
            $table->text('suggested_reply')->nullable();

            $table->string('ai_status')->default(TicketAiStatus::QUEUED->value);
            $table->text('ai_issue')->nullable();
            $table->json('ai_raw_response')->nullable();
            $table->timestamp('ai_processed_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
