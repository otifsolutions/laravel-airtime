<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReloadlyGiftCardProductsTable extends Migration {

    public function up() {
        if (!Schema::hasTable('reloadly_gift_card_products')){
            Schema::create('reloadly_gift_card_products', function (Blueprint $table) {
                $table->id();
                $table->string('rid');
                $table->unsignedBigInteger('country_id');
                $table->foreign('country_id')->references('id')->on('countries');
                $table->longText('title');
                $table->boolean('is_global')->default(false);
                $table->double('sender_fee');
                $table->double('discount_percentage');
                $table->string('denomination_type');
                $table->string('recipient_currency_code');
                $table->double('min_recipient_denomination')->nullable();
                $table->double('max_recipient_denomination')->nullable();
                $table->string('sender_currency_code');
                $table->double('min_sender_denomination')->nullable();
                $table->double('max_sender_denomination')->nullable();
                $table->json('fixed_recipient_denominations')->nullable();
                $table->json('fixed_sender_denominations')->nullable();
                $table->json('fixed_denominations_map')->nullable();
                $table->json('logo_urls')->nullable();
                $table->json('brand')->nullable();
                $table->json('country')->nullable();
                $table->json('redeem_instruction')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down() {
        if (Schema::hasTable('reloadly_gift_card_products')){
            Schema::dropIfExists('reloadly_gift_card_products');
        }
    }
};
