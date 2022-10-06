<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use OTIFSolutions\Laravel\Settings\Models\Setting;

class CreateValueTopupTransactionsTable extends Migration {

    public function up() {

        if (Schema::hasTable('value_topup_transactions')) {
            return;
        }

        Schema::create('value_topup_transactions', function (Blueprint $table) {

            if (Setting::get('myisam_engine')) {
                $table->engine = 'myIsam';
            }

            $table->id();

            // $table->bigInteger('order_id')->nullable();
            $table->foreignId('category_id')->references('id')->on('value_topup_categories');
            $table->foreignId('country_id')->references('id')->on('value_topup_countries');
            $table->foreignId('operator_id')->references('id')->on('value_topup_operators');
            $table->foreignId('product_id')->references('id')->on('value_topup_products');

            $table->string('reference')->unique();
            $table->double('topup');
            $table->double('amount');
            $table->double('number');

            $table->string('sender_currency');
            $table->string('receiver_currency');
            $table->enum('status', ['PENDING', 'SUCCESS', 'FAIL', 'CANCELLED', 'PROCESSING'])->default('PENDING');

            $table->json('response')->nullable();

            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('value_topup_transactions');
    }
};
