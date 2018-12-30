<?php

namespace App\Services;

interface ICrawlerService
{
    public function GetBalances($userId = null);
    public function GetAllBalances($userId);
    public function GetAllAssetsVersusBtcWithMarketData();
    public function GetAllAssetsVersusBtc();
}
