<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up() {

        if (Schema::hasTable('ding_connect_operators')) {
            return;
        }

        Schema::create('ding_connect_operators', function (Blueprint $table) {
            $table->id();

            $table->foreignId('country_id')
                ->references('id')
                ->on('ding_connect_countries');

            $table->string('provider_code');
            $table->string('name');
            $table->string('validation_regex');
            $table->string('customer_care_no')->nullable();
            $table->string('logo_url')->nullable();
            $table->json('region_code');
            $table->json('payment_type');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down() {
        Schema::dropIfExists('ding_connect_operators');
    }
};
