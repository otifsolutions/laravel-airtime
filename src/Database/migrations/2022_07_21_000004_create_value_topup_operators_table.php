<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use OTIFSolutions\Laravel\Settings\Models\Setting;

return new class extends Migration {

    public function up() {

        if (Schema::hasTable('value_topup_operators')) {
            return;
        }

        Schema::create('value_topup_operators', function (Blueprint $table) {

            if (Setting::get('myisam_engine')) {
                $table->engine = 'myIsam';
            }

            $table->id();

            $table->foreignId('country_id')
                ->references('id')
                ->on('value_topup_countries');

            $table->string('carrier_name');
            $table->string('image_url')->nullable();
            $table->string('denomination_type');
            $table->integer('product_id');

            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('value_topup_operators');
    }
};
