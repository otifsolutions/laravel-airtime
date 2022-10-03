<?php

namespace OTIFSolutions\LaravelAirtime\Commands;

use Illuminate\Console\Command;
use OTIFSolutions\Laravel\Settings\Models\Setting;
use OTIFSolutions\LaravelAirtime\Helpers\Reloadly;
use OTIFSolutions\LaravelAirtime\Models\{AirtimeCurrency,
    ReloadlyUtilityTransaction};

class SyncReloadlyUtilityTransaction extends Command {

    protected $signature = 'sync:reloadly_utility_transaction';

    protected $description = 'Sync data with the Reloadly transactions platform';

    public function handle() {

        if (!Setting::get('reloadly_service')) {
            $this->line("****************************************************************");
            $this->info("Reloadly Service is NULL or false. Enable it first");
            $this->line("****************************************************************");
            return 0;
        }
        $this->line('++++++++++++++++++++++++++++++++++++++++++++++');

        $this->line('');
        $this->line('****************************************************************');
        $this->info('Started Sync of Reloadly Utility Transaction API');
        $this->line('****************************************************************');

        $this->line('Checking if credentials exist in database');

        $credentials = [
            'key' => Setting::get('reloadly_api_key'),
            'secret' => Setting::get('reloadly_api_secret'),
            'mode' => Setting::get('reloadly_api_mode')
        ];

        if (!$credentials['key'] || !$credentials['secret'] || !$credentials['mode']) {
            return $this->returnError('Keys not found in settings.');
        }

        $this->info('Credentials Found');
        $this->line('Generating a New Token to be used');
        $reloadly = Reloadly::Make($credentials['key'], $credentials['secret'], $credentials['mode']);
        $credentials['utility_token'] = $reloadly->getUtilityToken();

        if (!$credentials['utility_token']) {
            return $this->returnError('Unable to generate a successful token');
        }

        $this->line('');
        $this->line('****************************************************************');
        $this->info('Started to Sync PROCESSING Reloadly Transactions of utility ');
        $this->line('****************************************************************');


        $this->line('Syncing with database.');
        $transactions = ReloadlyUtilityTransaction::where('status','PROCESSING')->get();
        $this->withProgressBar($transactions, function ($transaction) use ($reloadly) {
            $reloadly->confirmReloadlyUtilityTransaction($transaction);
        });

        $this->line(' ');
        $this->line('****************************************************************');
        $this->info('Sync Complete !!! ' . count($transactions) . ' transactions Synced.');
        $this->line('****************************************************************');
        $this->line('');
        return 0;
    }

    private function returnError(string $error): int {
        $this->error($error);
        return 0;
    }

}
