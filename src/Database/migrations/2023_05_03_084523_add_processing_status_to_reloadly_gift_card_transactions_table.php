<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProcessingStatusToReloadlyGiftCardTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('reloadly_gift_card_transactions')) {
            Schema::table('reloadly_gift_card_transactions', function (Blueprint $table) {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE `reloadly_gift_card_transactions` CHANGE `status` `status` ENUM('PENDING_PAYMENT','PENDING','PROCESSING','SUCCESS','FAIL','REFUNDED') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDING_PAYMENT'");
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('reloadly_gift_card_transactions')) {
            Schema::table('reloadly_gift_card_transactions', function (Blueprint $table) {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE `reloadly_gift_card_transactions` CHANGE `status` `status` ENUM('PENDING_PAYMENT','PENDING','SUCCESS','FAIL','REFUNDED') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDING_PAYMENT'");
            });
        }
    }
}
