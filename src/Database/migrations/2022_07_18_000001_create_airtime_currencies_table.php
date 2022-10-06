<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use OTIFSolutions\Laravel\Settings\Models\Setting;

class CreateAirtimeCurrenciesTable extends Migration {

    public function up() {
        Schema::create('airtime_currencies', function (Blueprint $table) {

            if (Setting::get('myisam_engine')) {
                $table->engine = 'myIsam';
            }

            $table->id();
            $table->bigInteger('base_currency_id')->nullable();
            $table->string('code');
            $table->double('rate');
            $table->double('profit')->default(0);
            $table->enum('status', ['ENABLED', 'DISABLED'])->default('DISABLED');
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('airtime_currencies');
    }
};
