<?php

use Illuminate\Foundation\Inspiring;
use App\Services\BittrexCrawlerService as BittrexCrawler;
use App\Services\Repositories\AssetsValuesRepository as AssetsValuesRepository;
use App\Services\Repositories\AssetsRepository as AssetsRepository;
use App\Services\Repositories\UsersAssetsRepository as UsersAssetsRepository;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');

Artisan::command('balances', function (BittrexCrawler $bittrexCrawler) {
    $this->comment(print_r($bittrexCrawler->GetAllBalances(1), true));
})->describe('Display all my account balances');

Artisan::command('update-assets-values', function (AssetsValuesRepository $assetsValuesRepository) {
    $assetsValuesRepository->UpdateAssetsValues();
})->describe('Save assets values in database');

Artisan::command('show-last-values', function (AssetsValuesRepository $assetsValuesRepository) {
    $this->comment(print_r($assetsValuesRepository->GetLastAssetValues(4), true));
})->describe('Show last series of values');

Artisan::command('update-assets', function (AssetsRepository $assetsRepository) {
    $assetsRepository->UpdateAssets();
})->describe('Update local copy of the available markets information');

Artisan::command('test', function (UsersAssetsRepository $usersAssetsRepository) {
    print_r($usersAssetsRepository->UpdateUserAssetsValues(1));
})->describe('run a test');

Artisan::command('all-markets', function (BittrexCrawler $bittrexCrawler) {
    $this->comment(print_r($bittrexCrawler->GetAllAssetsVersusBtcWithMarketData(), true));
})->describe('Display all my account balances');
