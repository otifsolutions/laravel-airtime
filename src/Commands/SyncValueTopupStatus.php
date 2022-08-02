<?php

namespace OTIFSolutions\LaravelAirtime\Commands;

use Illuminate\Console\Command;
use OTIFSolutions\Laravel\Settings\Models\Setting;
use OTIFSolutions\LaravelAirtime\Helpers\ValueTopup;
use OTIFSolutions\LaravelAirtime\Models\ValueTopupTransaction;

class SyncValueTopupStatus extends Command {

    protected $signature = 'sync:value-topup-status';

    protected $description = 'Sync status of transactions that are still in processing';

    public function handle() {

        if (!Setting::get('dtone_service')) {
            $this->line("****************************************************************");
            $this->info("Value-topup service is Diabled or false. Enable it first");
            $this->line("****************************************************************");
            return 0;
        }

        $this->line("");
        $this->line("****************************************************************");
        $this->info("Getting token to authenticate from ValueTopup Platform");
        $this->line("****************************************************************");

        $this->line("");
        $this->line("****************************************************************");
        $this->info("Started Sync of transactions with status processing");
        $this->line("****************************************************************");
        $this->line("Fetching transactions from database with status processing");

        $transactions = ValueTopupTransaction::where("status", "PROCESSING")->get();
        if (count($transactions) === 0) {
            $this->info("No Transactions with status Processing Found");
        }

        foreach ($transactions as $transaction) {
            $response = ValueTopup::Make()->getValueTopupStatus($transaction['reference']);
            if ($response['responseCode'] === '000')
                $transaction['status'] = 'SUCCESS';
            elseif ($response['responseCode'] == '851' || $response['responseCode'] == '852')
                $transaction['status'] = 'PROCESSING';
            else
                $transaction['status'] = 'FAIL';
            $transaction['response'] = $response;
            $transaction->save();
        }

        $this->line("****************************************************************");
        $this->info("All Transactions status synced");
        $this->line("****************************************************************");

    }
}
