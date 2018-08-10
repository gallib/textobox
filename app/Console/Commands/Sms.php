<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Sms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a sms';

    /**
     * @var \SoapClient
     */
    protected $client;

    /**
     * @var string
     */
    protected $login;

    /**
     * @var string
     */
    protected $password;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->client = new \SoapClient(getenv('TEXTOBOX_WSDL'));
        $this->login = getenv('TEXTOBOX_LOGIN');
        $this->password = getenv('TEXTOBOX_PASSWORD');
    }

    /**
     * Display remaining credits.
     *
     * @return void
     */
    protected function displayCredits()
    {
        $credits = $this->client->getCredits($this->login, $this->password);

        if ($credits > 0) {
             $this->info("You have $credits credits remaining");
        } else {
            $this->error('No credits remaining, please charge your account');
            exit;
        }
    }

    /**
     * Send a sms
     *
     * @return void
     */
    protected function sendSms()
    {
        $recipient = $this->ask("What is the recipient's number ?");
        $message = $this->ask("What message would you like to send ?");

        if (!$recipient || !$message) {
            $this->error('Both recipient and message are mandatory !');
            exit;
        }

        $sentSms = $this->client->sendSms(
            $this->login,
            $this->password,
            $recipient,
            $message
        );

        foreach ($sentSms as $sms) {
            if ($sms->status == 1) {
                $this->info("Your sms has been sent. Id: $sms->id");
            } else {
                $this->error("An error occured. Error code: $sms->status");
            }
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->displayCredits();

        $this->sendSms();
    }
}
