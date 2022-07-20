<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up() {
        Schema::create('reloadly_countries', function (Blueprint $table) {
            $table->engine = 'myIsam';
            $table->id();
            $table->string('iso');
            $table->string('name');
            $table->bigInteger('currency_id');
            $table->string('currency_name');
            $table->string('currency_symbol');
            $table->string('flag');
            $table->json('calling_codes');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down() {
        Schema::dropIfExists('reloadly_countries');
    }
};
