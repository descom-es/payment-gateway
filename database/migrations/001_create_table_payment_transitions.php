<?php

use Descom\Payment\TransitionStatus;
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

            $table->nullableMorphs('model');
            $table->string('merchant_id')->index();

            $table->foreignId('payment_id')->constrained();
            $table->decimal('amount',  20, 6);

            $table->enum('status',  [
                TransitionStatus::PENDING,
                TransitionStatus::PAID,
                TransitionStatus::DENIED,
                TransitionStatus::VOIDED,
            ])->default(TransitionStatus::PENDING);

            $table->json('gateway_request')->nullable();
            $table->string('gateway_id')->nullable();
            $table->json('gateway_response')->nullable();

            $table->timestamps();

            $table->unique(['payment_id', 'merchant_id']);
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
