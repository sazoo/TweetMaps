<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Tweet;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('cors');
    }

    public function getLatestTweets(){

        $count = \DB::table('tweets')
            ->where('created_at', '>=', \DB::raw('DATE_SUB(NOW(), INTERVAL 1 HOUR)'))
            ->count();
        $tweets = Tweet::orderBy('created_at', 'DESC')->take($count)->get();
        //var_dump($count);
        //$tweets = Tweet::orderBy('created_at', 'DESC')->take($count)->get();
        return response()->json($tweets);
    }
}
