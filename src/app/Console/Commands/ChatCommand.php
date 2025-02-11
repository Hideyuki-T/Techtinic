<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AIEngine;

class ChatCommand extends Command
{
    protected $signature = 'chat:run';
    protected $description = 'CLI上でAIパートナーと会話する';

    protected $aiEngine;

    public function __construct(AIEngine $aiEngine)
    {
        parent::__construct();
        $this->aiEngine = $aiEngine;
    }

    public function handle()
    {
        $this->info("AIパートナーとの対話を開始します。終了するには 'exit' と入力してください。");

        while (true) {
            $input = $this->ask('あなた');
            if (trim($input) === 'exit') {
                $this->info('対話を終了します。');
                break;
            }
            $response = $this->aiEngine->getResponse($input);
            $this->info("AI: {$response}");
        }

        return 0;
    }
}
