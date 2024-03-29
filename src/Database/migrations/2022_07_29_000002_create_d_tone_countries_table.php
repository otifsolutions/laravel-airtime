<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use OTIFSolutions\Laravel\Settings\Models\Setting;

class CreateDToneCountriesTable extends Migration {

    public function up() {

        if (Schema::hasTable('d_tone_countries')) {
            return;
        }

        Schema::create('d_tone_countries', function (Blueprint $table) {

            if (Setting::get('myisam_engine')) {
                $table->engine = 'myIsam';
            }

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
