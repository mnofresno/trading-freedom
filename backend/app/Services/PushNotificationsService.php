<?php

namespace App\Services;

use Illuminate\Foundation\Bus\DispatchesJobs;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;
use LaravelFCM\Message\Topics;

class PushNotificationsService
{
    public function __construct()
    {
    }

    public function SendBackgroundCommand($command, $tokens)
    {
        $this->Send("this is a background notification", "background notification", $command, $tokens, true);
    }

    public function Send($title, $description, $body, $tokens, $silentCommand = false)
    {
        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60*20);

        $notificationBuilder = new PayloadNotificationBuilder($title);
        $notificationBuilder->setBody($description)
                            ->setSound('default')
                            //->setClickAction('FCM_PLUGIN_ACTIVITY') // Only with FCM client Plugin
                            ->setTag("gastos");
                            //->setColor("#ff0000"); // FIJAR COLOR NOTIFICACION

        $dataBuilder = new PayloadDataBuilder();

        $payloadData = ['mensaje' => $body ];

        if($silentCommand)
        {
            $payloadData['isCommand'] = true;
        }

        $dataBuilder->addData($payloadData);

        $optionBuilder->setContentAvailable($silentCommand);
        $optionBuilder->setPriority('normal');
        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();

        // You must change it to get your tokens

        $downstreamResponse = FCM::sendTo($tokens, $option, $silentCommand ? null : $notification, $data);

        $success = $downstreamResponse->numberSuccess();
        $failure = $downstreamResponse->numberFailure();
        $modification = $downstreamResponse->numberModification();

        //return Array - you must remove all this tokens in your database
        $to_delete = $downstreamResponse->tokensToDelete();

        //return Array (key : oldToken, value : new token - you must change the token in your database )
        $to_modify = $downstreamResponse->tokensToModify();

        //return Array - you should try to resend the message to the tokens in the array
        $to_retry = $downstreamResponse->tokensToRetry();

        // return Array (key:token, value:errror) - in production you should remove from your database the tokens present in this array 
        $with_error = $downstreamResponse->tokensWithError();

        $results = compact('to_delete', 'to_modify', 'to_retry', 'with_error');

        $results_count = compact('success', 'failure', 'modification');

        return compact('results', 'results_count');
    }

    public function testWithPayload($token, $title, $description, $body)
    {
        return $this->Send($title,
                           $description,
                           $body,
                           [ $token ]);
    }

    public function test($token)
    {
        return $this->testWithPayload(
            $token,
            "Titulo de la notificacion",
            "descripcion del evento",
            "Este es un cuerpo de mensaje de prueba");
    }
}