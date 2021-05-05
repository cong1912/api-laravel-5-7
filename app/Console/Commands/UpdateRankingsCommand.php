<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class UpdateRankingsCommand extends Command
{

    protected $signature = 'update:rankings';


    public function handle()
    {
        $ambassador=User::ambassadors()->get();

        $bar=$this->output->createProgressBar($ambassador->count());

        $bar->start();

        $ambassador->each(function (User $user)use($bar){
            Redis::zadd('rankings',(int)$user->revenue,$user->name);
            $bar->advance();
        });
        $bar->finish();
    }
}
