<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up() {

        if (Schema::hasTable('reloadly_promotions')) {
            return;
        }

        Schema::create('reloadly_promotions', function (Blueprint $table) {
            $table->engine = 'myIsam';
            $table->id();
            $table->string('rid');

            $table->foreignId('operator_id')
                ->references('id')
                ->on('reloadly_operators');

            $table->longText('title');
            $table->longText('title2')->nullable();
            $table->longText('description')->nullable();
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();
            $table->string('denominations')->nullable();
            $table->string('local_denominations')->nullable();
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('reloadly_promotions');
    }
};
