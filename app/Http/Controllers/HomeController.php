<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Tweet;

class HomeController extends Controller
{
    public function getLatestTweets(){
        $count = \DB::table('tweets') ->where(\DB::raw('date >= DATE_SUB(NOW(), INTERVAL 1 HOUR)'));
        $tweets = Tweet::orderBy('created_at', 'DESC')->take($count)->get();
        return response()->json($tweets);
    }
}
