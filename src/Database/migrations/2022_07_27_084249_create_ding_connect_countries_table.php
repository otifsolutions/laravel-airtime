<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up() {
        Schema::create('ding_connect_countries', function (Blueprint $table) {
            $table->engine = 'myIsam';
            $table->id();
            $table->string('name');
            $table->string('iso2', 22);
            $table->string('dial_code', 22);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down() {
        Schema::dropIfExists('ding_connect_countries');
    }
};