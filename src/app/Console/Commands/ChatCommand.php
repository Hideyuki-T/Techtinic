<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AIEngine;

class ChatCommand extends Command
{
    protected $signature = 'chat:run';
    protected $description = 'CLI上でTechtinicと会話する';

    protected $aiEngine;

    public function __construct(AIEngine $aiEngine)
    {
        parent::__construct();
        $this->aiEngine = $aiEngine;
    }

    public function handle()
    {
        $this->info("それじゃあ話そうか。やめたくなったら 'exit' だよ！");

        while (true) {
            $input = $this->ask('あなた');
            if (trim($input) === 'exit') {
                $this->info('またね。');
                break;
            }
            $response = $this->aiEngine->getResponse($input);
            $this->info("Techtinic: {$response}");
        }

        return 0;
    }
}
