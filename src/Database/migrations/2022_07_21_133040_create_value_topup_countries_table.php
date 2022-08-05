<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up() {

        if (Schema::hasTable('value_topup_countries')) {
            return;
        }

        Schema::create('value_topup_countries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->references('id')
                ->on('value_topup_categories');

            $table->string('name')->nullable();
            $table->string('country_code');

            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('value_topup_countries');
    }
};
