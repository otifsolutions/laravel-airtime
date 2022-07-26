<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up() {
        Schema::create('value_topup_products', function (Blueprint $table) {
            $table->engine = 'myIsam';
            $table->id();

            $table->foreignId('operator_id')
                ->references('id')
                ->on('value_topup_operators');

            $table->integer('sku_id');
            $table->string('product_name');
            $table->text('description')->nullable();
            $table->integer('face_value');
            $table->double('min_amount');
            $table->double('max_amount');
            $table->integer('discount');
            $table->string('pricing');
            $table->string('category');
            $table->tinyInteger('is_sales_tax_charged');
            $table->double('sales_tax');
            $table->double('exchange_rate');
            $table->string('currency_code');
            $table->string('country_code');
            $table->double('local_phone_number_length');
            $table->json('international_country_code'); // caste json
            $table->tinyInteger('allow_decimal');
            $table->double('fee');
            $table->string('operator_name');
            $table->string('delivery_currency_code');
            $table->string('supported_transaction_currencies');
            $table->string('carrier_name');
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('value_topup_products');
    }

};
