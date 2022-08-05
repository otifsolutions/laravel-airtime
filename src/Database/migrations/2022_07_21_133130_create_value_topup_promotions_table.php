<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up() {

        if (Schema::hasTable('value_topup_promotions')) {
            return;
        }

        Schema::create('value_topup_promotions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('operator_id')
                ->references('id')
                ->on('value_topup_operators');

            $table->text('name');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->text('description')->nullable();
            $table->string('restriction');
            $table->json('promotion_min_max');
            $table->json('product');

            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('value_topup_promotions');
    }
};
