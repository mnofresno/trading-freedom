<?php

namespace App\Services;

interface ICrawlerService
{
    public function GetBalances($user_id = null);
    public function GetAllBalances($user_id);
    public function GetAllAssetsVersusBtcWithMarketData();
    public function GetAllAssetsVersusBtc();
}
