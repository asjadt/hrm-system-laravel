<?php

namespace App\Http\Middleware;

use App\Models\ErrorLog;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Session;

class ResponseMiddleware
{


    public function handle($request, Closure $next)
    {



     $apiBaseUrl = config('app.url');

        $response = $next($request);



        if ($response->headers->get('content-type') === 'application/json') {
            Session::flush();
            $content = $response->getContent();
            $convertedContent = $this->convertDatesInJson($content);
            $response->setContent($convertedContent);





            if ((($response->getStatusCode() >= 500 && $response->getStatusCode() < 600)) ) {
                $errorLog = [
                    "api_url" => $request->fullUrl(),
                    "fields" => json_encode(request()->all()),
                    "token" => request()->bearerToken()?request()->bearerToken():"",
                    "user" => auth()->user() ? json_encode(auth()->user()) : "",
                    "user_id" => auth()->user() ?auth()->user()->id:"",
                    "status_code" => $response->getStatusCode(),

                    "ip_address" => request()->ip(),

                    "request_method" => $request->method(),
                    "message" =>  $response->getContent(),
                ];




            }
            else if(($response->getStatusCode() >= 300 && $response->getStatusCode() < 500)) {
                $errorLog = [
                    "api_url" => $request->fullUrl(),
                    "fields" => json_encode(request()->all()),
                    "token" => request()->bearerToken()?request()->bearerToken():"",
                    "user" => auth()->user() ? json_encode(auth()->user()) : "",
                    "user_id" => auth()->user() ?auth()->user()->id:"",
                    "status_code" => $response->getStatusCode(),
                    "ip_address" => request()->ip(),
                    "request_method" => $request->method(),
                    "message" =>  $response->getContent(),
                ];



            }

        }

        return $response;
    }

    private function convertDatesInJson($json)
    {
        $data = json_decode($json, true);


        if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
            array_walk_recursive($data, function (&$value, $key) {

                if (is_string($value) && (Carbon::hasFormat($value, 'Y-m-d') || Carbon::hasFormat($value, 'Y-m-d\TH:i:s.u\Z') || Carbon::hasFormat($value, 'Y-m-d\TH:i:s'))) {


                 $date = Carbon::parse($value);


                    if ($date->year <= 0) {
                        $value = "";
                    } else {
                 
                       if ($date->hour == 0 && $date->minute == 0 && $date->second == 0) {
                        $value = $date->format('d-m-Y');
                    } else {
                        $value = $date->format('d-m-Y H:i:s');
                    }
                    }

                }
            });

            return json_encode($data);
        }

        return $json;
    }


}
