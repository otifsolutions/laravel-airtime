<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use OTIFSolutions\Laravel\Settings\Models\Setting;

class CreateReloadlyDiscountsTable extends Migration {

    public function up() {

        if (Schema::hasTable('reloadly_discounts')) {
            return;
        }

        Schema::create('reloadly_discounts', function (Blueprint $table) {

            if (Setting::get('myisam_engine')) {
                $table->engine = 'myIsam';
            }

            $table->id();
            $table->string('rid');

            $table->foreignId('operator_id')
                ->references('id')
                ->on('reloadly_operators');

            $table->double('percentage')->nullable();
            $table->double('international_percentage')->nullable();
            $table->double('local_percentage')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down() {
        Schema::dropIfExists('reloadly_discounts');
    }
};
