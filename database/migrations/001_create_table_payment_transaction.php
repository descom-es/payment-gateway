<?php

use Descom\Payment\TransactionStatus;
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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();

            $table->nullableMorphs('model');
            $table->string('merchant_id')->index();

            $table->foreignId('payment_id')->constrained();
            $table->decimal('amount',  20, 6);

            $table->enum('status',  [
                TransactionStatus::PENDING,
                TransactionStatus::PAID,
                TransactionStatus::DENIED,
                TransactionStatus::VOIDED,
            ])->default(TransactionStatus::PENDING);

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
        Schema::drop('payment_transactions');
    }
};
