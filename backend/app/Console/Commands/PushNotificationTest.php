<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class PushNotificationTest extends Command
{
    protected $signature = 'push-notification {token} {--silent}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Push notifications testing command';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(\App\Services\PushNotificationsService $push)
    {
        $token = $this->argument('token');

        $silent = $this->option('silent');

        //$this->info(print_r($push->SendTopic(), true));
        if($silent)
        {
            $this->info(print_r($push->SendBackgroundCommand('update_saldo', [$token]), true));
        }
        else
        {
            $this->info(print_r($push->Send('titulo','descripcion','cuerpo',[$token]), true));
        }
    }
}
