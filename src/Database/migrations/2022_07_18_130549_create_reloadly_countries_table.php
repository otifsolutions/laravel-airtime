<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use OTIFSolutions\Laravel\Settings\Models\Setting;

return new class extends Migration {

    public function up() {

        if (!Setting::get('reloadly_service')) {
            return;
        }

        Schema::create('reloadly_countries', function (Blueprint $table) {

            $table->engine = 'myIsam';
            $table->id();
            $table->string('iso');
            $table->string('name');

            $table->foreignId('currency_id')
                ->references('id')
                ->on('currencies');

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
