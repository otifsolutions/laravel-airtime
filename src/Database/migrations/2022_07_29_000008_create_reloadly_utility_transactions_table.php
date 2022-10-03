<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up() {
        if (!Schema::hasTable('reloadly_utility_transactions')){
            Schema::create('reloadly_utility_transactions', function (Blueprint $table) {
                $table->id();
                $table->string('t_id')->nullable();
                $table->unsignedBigInteger('utility_id');
                $table->foreign('utility_id')->references('id')->on('reloadly_utilities');
                $table->unsignedBigInteger('user_id');
                $table->foreign('user_id')->references('id')->on('users');
                $table->string('status')->default('PENDING');
                $table->string('reference_id')->nullable();
                $table->string('subscriber_account_number');
                $table->string('code')->nullable();
                $table->string('message')->nullable();
                $table->boolean('is_local');
                $table->double('amount');
                $table->string('amount_currency_code')->nullable();
                $table->double('delivery_amount')->nullable();
                $table->string('delivery_amount_currency_code')->nullable();
                $table->double('fee')->nullable();
                $table->string('fee_currency_code')->nullable();
                $table->double('discount')->nullable();
                $table->string('discount_currency_code')->nullable();
                $table->string('submitted_at')->nullable();
                $table->json('response')->nullable();
                $table->json('balance_info')->nullable();
                $table->json('biller_details')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down() {
        if (Schema::hasTable('reloadly_utility_transactions')){
            Schema::dropIfExists('reloadly_utility_transactions');
        }
    }
};
