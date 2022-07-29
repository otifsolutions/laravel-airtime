<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up() {
        Schema::create('d_tone_countries', function (Blueprint $table) {
            $table->engine = 'myIsam';
            $table->id();
            $table->string('name');
            $table->string('iso2', 22);
            $table->string('dial_code', 22);
            $table->integer('t_shop_id');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down() {
        Schema::dropIfExists('d_tone_countries');
    }
};
