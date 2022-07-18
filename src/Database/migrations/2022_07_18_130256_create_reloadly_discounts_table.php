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
        Schema::create('reloadly_discounts', function (Blueprint $table) {
            $table->engine = 'myIsam';
            $table->id();
            $table->string('rid');
            $table->bigInteger('operator_id');
            $table->double('percentage')->nullable();
            $table->double('international_percentage')->nullable();
            $table->double('local_percentage')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('reloadly_discounts');
    }
};
