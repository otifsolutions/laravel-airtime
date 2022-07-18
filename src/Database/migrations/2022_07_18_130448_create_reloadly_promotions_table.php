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
        Schema::create('reloadly_promotions', function (Blueprint $table) {
            $table->engine = 'myIsam';
            $table->id();
            $table->string('rid');
            $table->bigInteger('operator_id');
            $table->longText('title');
            $table->longText('title2')->nullable();
            $table->longText('description')->nullable();
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();
            $table->string('denominations')->nullable();
            $table->string('localDenominations')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('reloadly_promotions');
    }
};
