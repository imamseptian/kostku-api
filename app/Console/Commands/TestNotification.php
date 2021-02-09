<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

class TestNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coba:coba1';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $mytime = Carbon::now('Asia/Jakarta')->format('H:i:s');

        $notif = array(
            'title' => "NOTIFIKAS JAM 1:30 : " . $mytime,
            'body' => '',
        );


        $data = array(
            'stack' => 'MainScreen',
            'screen' => 'PendaftarStackScreen',
        );

        $fields = array('to' => '/topics/kostku-1', 'notification' => $notif, 'data' => $data);
        // //api_key in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
        $server_key = 'AAAA8xTtYLY:APA91bGBMemsYq9dHBuJDHSxajLLeljIoC2hFLEIggcozlNFk5w9Cdc25K0hFMgbVfAyUGLr6P6Zjtfye1VGavnUQc81ivvEjSI2MYtPeRt6suIccraFBHd2f-35dIeFBw94_grF6-oT';

        $payload = json_encode($fields);
        $ch = curl_init('https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        // Set HTTP Header for POST request
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload),
                'Authorization:key=' . $server_key
            )
        );

        // Submit the POST request
        $result = curl_exec($ch);

        // Close cURL session handle
        curl_close($ch);
        echo "done";
    }
}
