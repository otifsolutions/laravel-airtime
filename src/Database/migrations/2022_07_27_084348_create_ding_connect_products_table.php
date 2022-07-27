<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up() {
        Schema::create('ding_connect_products', function (Blueprint $table) {

            $table->engine = 'myIsam';

            $table->id();

            $table->foreignId('country_id')
                ->references('id')
                ->on('ding_connect_countries');

            $table->foreignId('operator_id')
                ->references('id')
                ->on('ding_connect_operators');

            $table->foreignId('category_id')
                ->references('id')
                ->on('ding_connect_categories');

            $table->string('category_name')->nullable();

            $table->foreignId('currency_id')
                ->references('id')
                ->on('currencies');

            $table->foreignId('destination_currency_id')
                ->references('id')
                ->on('currencies');

            $table->double('fx_rate');
            $table->double('maximum_value');
            $table->double('minimum_value');
            $table->double('local_maximum_value');
            $table->double('local_minimum_value');
            $table->string('sku_code');
            $table->string('localization_key');
            $table->json('maximum');
            $table->json('minimum');
            $table->double('commission_rate');
            $table->json('benefits');
            $table->string('uat_number');
            $table->string('default_display_text');
            $table->string('region_code');
            $table->json('payment_types');
            $table->tinyInteger('lookup_bills_required')->default(0);
            $table->timestamps();
            $table->softDeletes();

        });
    }

    public function down() {
        Schema::dropIfExists('ding_connect_products');
    }
};
