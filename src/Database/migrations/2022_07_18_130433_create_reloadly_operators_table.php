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
        Schema::create('reloadly_operators', function (Blueprint $table) {
            $table->engine = 'myIsam';
            $table->id();
            $table->string('rid');
            $table->bigInteger('country_id');
            $table->string('name');
            $table->string('bundle');
            $table->tinyInteger('data')->nullable();
            $table->tinyInteger('pin')->nullable();
            $table->tinyInteger('supports_local_amounts')->nullable();
            $table->string('denomination_type');
            $table->bigInteger('sender_currency_id');
            $table->string('sender_currency_symbol');
            $table->bigInteger('destination_currency_id');
            $table->string('destination_currency_symbol');
            $table->double('commission');
            $table->double('international_discount');
            $table->double('local_discount')->nullable();
            $table->double('most_popular_amount')->nullable();
            $table->double('min_amount')->nullable();
            $table->double('local_min_amount')->nullable();
            $table->double('max_amount')->nullable();
            $table->double('local_max_amount')->nullable();
            $table->double('fx_rate')->nullable();
            $table->longText('logo_urls')->nullable()->comment('(DC2Type:json)');
            $table->json('fixed_amounts');
            $table->json('fixed_amounts_descriptions');
            $table->json('local_fixed_amounts');
            $table->json('local_fixed_amounts_descriptions');
            $table->json('suggested_amounts');
            $table->json('suggested_amounts_map')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('reloadly_operators');
    }
};
