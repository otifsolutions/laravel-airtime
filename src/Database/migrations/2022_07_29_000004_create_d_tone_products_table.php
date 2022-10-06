<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use OTIFSolutions\Laravel\Settings\Models\Setting;

class CreateDToneProductsTable extends Migration {

    public function up() {

        if (Schema::hasTable('d_tone_products')) {
            return;
        }

        Schema::create('d_tone_products', function (Blueprint $table) {

            if (Setting::get('myisam_engine')) {
                $table->engine = 'myIsam';
            }

            $table->id();

            $table->foreignId('country_id')->references('id')->on('d_tone_countries');
            $table->foreignId('operator_id')->references('id')->on('d_tone_operators');
            $table->foreignId('sender_currency_id')->references('id')->on('airtime_currencies');
            $table->foreignId('destination_currency_id')->references('id')->on('airtime_currencies');

            $table->string('product');
            $table->string('retail_price');
            $table->string('wholesale_price');

            $table->timestamps();
            $table->softDeletes();

        });
    }

    public function down() {
        Schema::dropIfExists('d_tone_products');
    }
};
