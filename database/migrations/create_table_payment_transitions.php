<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_transitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained();
            $table->decimal('amount',  20, 6);
            $table->string('merchant_id')->index();
            $table->json('gateway_request')->nullable();
            $table->enum('status',  ['pending_payment', 'success', 'denied', 'cancelled'])->default('pending_payment');
            $table->string('gateway_id')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('payments');
    }
};
