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

        Schema::create('reloadly_discounts', function (Blueprint $table) {
            $table->engine = 'myIsam';
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
