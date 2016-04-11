<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use Log;
use Redis;
use App\Tweet;
use App\RawTweet;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // Commands\Inspire::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function () {
			while(Redis::llen('tweets') > 0){
                try{
                    $raw = Redis::lpop('tweets');
                    $tweetArr = json_decode($raw, true);

                    $tweet = new Tweet;
                    $tweet->tweet_id = $tweetArr['id'];
                    $tweet->date_created = $tweetArr['created_at'];
                    $tweet->coordinates = $tweetArr['coordinates'];
                    $tweet->user_id = $tweetArr['user']['id'];
                    $tweet->tweet = $tweetArr['text'];
                    $tweet->screen_name = $tweetArr['user']['screen_name'];

                    $tweet->save();

                    $rawTweet = new RawTweet;
                    $rawTweet->raw = $raw;

                    $rawTweet->save();
                }catch(\Exception $e) {
                    Log::error($e->getMessage());
                }
			}
		})->everyMinute();
    }
}
