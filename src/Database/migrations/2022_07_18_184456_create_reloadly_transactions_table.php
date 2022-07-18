<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('reloadly_transactions', function (Blueprint $table) {
            $table->engine = 'myIsam';
            $table->id();
            $table->bigInteger('order_id');
            $table->bigInteger('operator_id');
            $table->tinyInteger('is_local')->nullable();
            $table->double('topup');
            $table->double('amount');
            $table->string('number')->nullable();
            $table->string('sender_currency')->nullable();
            $table->string('receiver_currency')->nullable();
            $table->enum('status', ['PENDING', 'SUCCESS', 'FAIL', 'PENDING_ORDER', 'CANCELLED'])->default('PENDING_ORDER');
            $table->json('response')->nullable();
            $table->json('pin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('reloadly_transactions');
    }
};
