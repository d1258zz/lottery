<?php

/*
 * This file is part of nodeloc/lottery.
 *
 * Copyright (c) Nodeloc.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace D1258zz\Lottery\Console;

use D1258zz\Lottery\Lottery;
use D1258zz\Lottery\LotteryOption;
use Illuminate\Console\Command;

class RefreshParticipantsCountCommand extends Command
{
    protected $signature = 'd1258zz:lottery:refresh';

    protected $description = 'Re-calculate the total number of participants in lottery.';

    public function handle()
    {
        $progress = $this->output->createProgressBar(Lottery::query()->count());

        Lottery::query()->each(function (Lottery $lottery) use ($progress) {
            $lottery->refreshParticipantsCount()->save();
            $progress->advance();
        });

        $progress->finish();

        $this->info('Done.');
    }
}
