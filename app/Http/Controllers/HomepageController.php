<?php

namespace App\Http\Controllers;

use App\Services\OAuthService;
use Illuminate\Http\Request;

class HomepageController extends Controller
{
     function index(Request $request, OAuthService $OAuthService) {
//        $results = $OAuthService->createEvent($request);
//        if (empty($results)) {
//            return ['Failed in php'];
//        } else {
//            var_dump($results->getHtmlLink());
//        }

        return view('welcome');
    }

    function ajax(Request  $request, OAuthService $OAuthService) {
        $data = urldecode($request->getQueryString());
        $data = substr_replace($data, "}", -1);
        $data = json_decode($data);
        $results = $OAuthService->createEvent($data);
        if (empty($results)) {
            return ['Api failed'];
        } else {
            return $results->getHtmlLink();
        }
    }

}
