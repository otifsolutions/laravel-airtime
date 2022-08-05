<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up() {

        if (Schema::hasTable('d_tone_operators')) {
            return;
        }

        Schema::create('d_tone_operators', function (Blueprint $table) {
            $table->id();
            $table->integer('t_shop_id');
            $table->foreignId('country_id')->references('id')->on('d_tone_countries');
            $table->string('name');
            $table->string('type');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down() {
        Schema::dropIfExists('d_tone_operators');
    }
};
