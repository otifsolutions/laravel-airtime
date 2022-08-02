<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use OTIFSolutions\Laravel\Settings\Models\Setting;

return new class extends Migration {

    public function up() {

        if (!Setting::get('reloadly_service')) {
            return;
        }

        Schema::create('reloadly_operators', function (Blueprint $table) {
            $table->engine = 'myIsam';
            $table->id();
            $table->string('rid');

            $table->foreignId('country_id')
                ->references('id')
                ->on('reloadly_countries');

            $table->string('name');
            $table->string('bundle');
            $table->tinyInteger('data')->nullable();
            $table->tinyInteger('pin')->nullable();
            $table->tinyInteger('supports_local_amounts')->nullable();
            $table->string('denomination_type');

            $table->foreignId('sender_currency_id')
                ->references('id')
                ->on('currencies');

            $table->string('sender_currency_symbol');

            $table->foreignId('destination_currency_id')
                ->references('id')
                ->on('currencies');

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
            $table->json('logo_urls')->nullable()->comment('(DC2Type:json)');
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

    public function down() {
        Schema::dropIfExists('reloadly_operators');
    }
};
