<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReloadlyUtilitiesTable extends Migration {

    public function up() {
        if (!Schema::hasTable('reloadly_utilities')){
            Schema::create('reloadly_utilities', function (Blueprint $table) {
                $table->id();
                $table->string('rid');
                $table->string('name');
                $table->unsignedBigInteger('country_id');
                $table->foreign('country_id')->references('id')->on('countries');
                $table->string('country_code');
                $table->string('country_name');
                $table->string('type');
                $table->string('service_type');
                $table->boolean('local_amount_supported');
                $table->string('local_transaction_currency_code');
                $table->double('min_local_transaction_amount');
                $table->double('max_local_transaction_amount');
                $table->double('local_transaction_fee')->nullable();
                $table->string('local_transaction_fee_currency_code')->nullable();
                $table->double('fx_rate')->nullable();
                $table->string('fx_currency_code')->nullable();
                $table->double('local_discount_percentage')->nullable();
                $table->boolean('international_amount_supported');
                $table->string('international_transaction_currency_code')->nullable();
                $table->double('min_international_transaction_amount');
                $table->double('max_international_transaction_amount');
                $table->double('international_transaction_fee');
                $table->string('international_transaction_fee_currency_code');
                $table->double('international_discount_percentage');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down() {
        if (Schema::hasTable('reloadly_utilities')){
            Schema::dropIfExists('reloadly_utilities');
        }
    }
};
