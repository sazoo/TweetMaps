<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use Log;
use Redis;
use App\Tweet;
use App\RawTweet;
use Thujohn\Twitter\Twitter;

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
        //get tweets
        $schedule->call(function () {
            while(Redis::llen('tweets') > 0){
                try{
                    $raw = Redis::lpop('tweets');
                    //$raw = '{"created_at":"Wed Apr 13 06:26:31 +0000 2016","id":720136062060744704,"id_str":"720136062060744704","text":"How cute... #TheManor #Baguio #HotelLiving #Philippines #TravelDiary @ The Manor Hotel Camp John\u2026 https:\/\/t.co\/oECRFPTWHH","source":"\u003ca href=\"http:\/\/instagram.com\" rel=\"nofollow\"\u003eInstagram\u003c\/a\u003e","truncated":false,"in_reply_to_status_id":null,"in_reply_to_status_id_str":null,"in_reply_to_user_id":null,"in_reply_to_user_id_str":null,"in_reply_to_screen_name":null,"user":{"id":521085144,"id_str":"521085144","name":"Steel Town Girl","screen_name":"_r_a_s_h_m_i_","location":"Manila","url":null,"description":"Progress your pace, posture, position.. #thereislittlefashioninallofus #life","protected":false,"verified":false,"followers_count":118,"friends_count":151,"listed_count":36,"favourites_count":413,"statuses_count":6180,"created_at":"Sun Mar 11 06:57:21 +0000 2012","utc_offset":-36000,"time_zone":"Hawaii","geo_enabled":true,"lang":"en","contributors_enabled":false,"is_translator":false,"profile_background_color":"C0DEED","profile_background_image_url":"http:\/\/abs.twimg.com\/images\/themes\/theme1\/bg.png","profile_background_image_url_https":"https:\/\/abs.twimg.com\/images\/themes\/theme1\/bg.png","profile_background_tile":false,"profile_link_color":"0084B4","profile_sidebar_border_color":"C0DEED","profile_sidebar_fill_color":"DDEEF6","profile_text_color":"333333","profile_use_background_image":true,"profile_image_url":"http:\/\/pbs.twimg.com\/profile_images\/655348384925028352\/kc6y54Vz_normal.jpg","profile_image_url_https":"https:\/\/pbs.twimg.com\/profile_images\/655348384925028352\/kc6y54Vz_normal.jpg","profile_banner_url":"https:\/\/pbs.twimg.com\/profile_banners\/521085144\/1445082558","default_profile":true,"default_profile_image":false,"following":null,"follow_request_sent":null,"notifications":null},"geo":{"type":"Point","coordinates":[16.40052443,120.61790024]},"coordinates":{"type":"Point","coordinates":[120.61790024,16.40052443]},"place":{"id":"003d47f62835a9f1","url":"https:\/\/api.twitter.com\/1.1\/geo\/id\/003d47f62835a9f1.json","place_type":"city","name":"Baguio City","full_name":"Baguio City, Cordillera Admin Region","country_code":"PH","country":"Republika ng Pilipinas","bounding_box":{"type":"Polygon","coordinates":[[[120.542369,16.365619],[120.542369,16.438941],[120.634512,16.438941],[120.634512,16.365619]]]},"attributes":{}},"contributors":null,"is_quote_status":false,"retweet_count":0,"favorite_count":0,"entities":{"hashtags":[{"text":"TheManor","indices":[12,21]},{"text":"Baguio","indices":[22,29]},{"text":"HotelLiving","indices":[30,42]},{"text":"Philippines","indices":[43,55]},{"text":"TravelDiary","indices":[56,68]}],"urls":[{"url":"https:\/\/t.co\/oECRFPTWHH","expanded_url":"https:\/\/www.instagram.com\/p\/BEIV0xsm1jx\/","display_url":"instagram.com\/p\/BEIV0xsm1jx\/","indices":[98,121]}],"user_mentions":[],"symbols":[]},"favorited":false,"retweeted":false,"possibly_sensitive":false,"filter_level":"low","lang":"en","timestamp_ms":"1460528791333"}';
                    $tweetArr = json_decode($raw, true);
                    if(isset($tweetArr['geo'])){
                        $tweet = new Tweet;
                        $tweet->tweet_id = $tweetArr['id'];
                        $tweet->date_created = $tweetArr['created_at'];

                        $tweet->longitude = $tweetArr['coordinates']['coordinates'][0];
                        $tweet->latitude = $tweetArr['coordinates']['coordinates'][1];

                        $tweet->user_id = $tweetArr['user']['id'];
                        $tweet->tweet = $tweetArr['text'];
                        $tweet->screen_name = $tweetArr['user']['screen_name'];

                        $tweet->save();
                        //var_dump($tweetArr['coordinates']['coordinates'][0]);
                    }

                    $rawTweet = new RawTweet;
                    $rawTweet->raw = $raw;
                    $rawTweet->tweet_id = $tweetArr['id'];

                    //$rawTweet->save();
                }catch(\Exception $e) {
                    Log::error($e->getMessage());
                }
            }
        })->everyMinute();

        //update tags to search
        $schedule->call(function (){
            $trends = json_decode(Twitter::getTrendsPlace(23424934));
            foreach($trends as $trend){
                Redis::lpush('tweets', $trend['name']);
            }
        })->everyFiveMinutes();

        $schedule->call(function (){
            \DB::table('tweets')
                ->where('created_at', '<=', \DB::raw('DATE_SUB(NOW(), INTERVAL 5 HOUR)'))
                ->delete();
        })->everyFiveMinutes();
    }
}
