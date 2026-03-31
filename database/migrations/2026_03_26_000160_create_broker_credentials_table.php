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
        Schema::create('broker_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broker_connection_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('status')->default('active');
            $table->text('credential_payload');
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('broker_credentials');
    }
};
