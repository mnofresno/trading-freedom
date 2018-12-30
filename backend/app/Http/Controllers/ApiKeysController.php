<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use App\Models\ExchangeProvider;
use Illuminate\Http\Request;
use App\Models\UserKey;

class ApiKeysController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->all();
        $data['user_id'] = $this->getCurrentUser()->id;
        $data['description'] = '';
        UserKey::create($data);
        return '';
    }

    public function destroy($id)
    {
        $exchange = ExchangeProvider::find($id);
        $userKeyToDelete = UserKey::where('user_id', '=', $this->getCurrentUser()->id)
            ->where('exchange_provider_id', '=', $id)
            ->firstOrFail();
        $userKeyToDelete->delete();
    }
}
