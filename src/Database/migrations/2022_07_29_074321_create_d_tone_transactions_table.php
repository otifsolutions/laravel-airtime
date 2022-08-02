<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use OTIFSolutions\Laravel\Settings\Models\Setting;

return new class extends Migration {

    public function up() {

        if (!Setting::get('dtone_service')) {
            return;
        }

        Schema::create('d_tone_transactions', function (Blueprint $table) {
            $table->engine = 'myIsam';
            $table->id();

            $table->foreignId('operator_id')->references('id')->on('d_tone_operators');
            $table->foreignId('product_id')->references('id')->on('d_tone_products');

            $table->string('sender_phone_no');
            $table->string('number')->nullable();

            $table->string('product');
            $table->enum('status', ['PENDING', 'SUCCESS', 'FAIL', 'PENDING_ORDER', 'CANCELLED'])->default('PENDING_ORDER');

            $table->json('response')->nullable();

            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('d_tone_transactions');
    }
};
