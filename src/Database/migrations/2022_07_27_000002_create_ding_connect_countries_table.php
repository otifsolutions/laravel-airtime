<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use OTIFSolutions\Laravel\Settings\Models\Setting;

class CreateDingConnectCountriesTable extends Migration {

    public function up() {

        if (Schema::hasTable('ding_connect_countries')) {
            return;
        }

        Schema::create('ding_connect_countries', function (Blueprint $table) {

            if (Setting::get('myisam_engine')) {
                $table->engine = 'myIsam';
            }

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
