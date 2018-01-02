<?php

use Illuminate\Foundation\Inspiring;
use App\Services\BittrexCrawlerService as BittrexCrawler;
use App\Services\Repositories\AssetsValuesRepository as AssetsValuesRepository;
use App\Services\Repositories\AssetsRepository as AssetsRepository;

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
    $this->comment(print_r($bittrexCrawler->GetAllBalances(), true));
})->describe('Display all my account balances');

Artisan::command('update-balances', function (AssetsValuesRepository $assetsValuesRepository) {
    $assetsValuesRepository->UpdateAssetsValues();
})->describe('Save account balances in database');

Artisan::command('update-assets', function (AssetsRepository $assetsRepository) {
    $assetsRepository->UpdateAssets();
})->describe('Update local copy of the available markets information');
