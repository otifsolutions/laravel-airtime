<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up() {

        if (Schema::hasTable('ding_connect_transactions')) {
            return;
        }

        Schema::create('ding_connect_transactions', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('order_id')->nullable();

            $table->foreignId('operator_id')
                ->references('id')
                ->on('ding_connect_operators');

            $table->foreignId('product_id')
                ->references('id')
                ->on('ding_connect_products');

            $table->string('sku_code');
            $table->double('send_value');
            $table->string('send_currency_code');
            $table->string('number')->nullable();
            $table->string('ref');
            $table->enum('status', ['PENDING', 'SUCCESS', 'FAIL', 'PENDING_ORDER', 'CANCELLED'])->default('PENDING_ORDER');
            $table->json('response')->nullable();
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('ding_connect_transactions');
    }
};
