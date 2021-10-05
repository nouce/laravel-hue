<?php
namespace Fyr\PhilipsHue;

use Illuminate\Support\Facades\Http;
use GuzzleHttp\Psr7\Utils;


class ApiRequest {
    protected $url;
    protected $access_token;
    protected $headers;

    public function __construct($user)
    {
        $this->url = 'https://api.meethue.com/bridge/' . $user->philips_hue_username;
        $this->access_token = $user->philips_hue_access_token;
        
        $this->headers = [
            'Authorization' => 'Bearer ' . $this->access_token
        ];
    }

    // public function getToken()
    // {
    //     $response = Http::get($this->url . 'retreive_token', [
    //         'uuid' => $this->uuid,
    //         'api_token' => $this->config('dk.api_key')
    //     ]);
    //     dd($response);
    //     return $response->body();
    // }

    public function get(string $endpoint, array $params = [])
    {
        $response = Http::withHeaders($this->headers)->get($this->url . $endpoint, $params);

        // if($response->serverError())
        // {
        //     $response->throw(function ($response, $e){
        //         $stream = Utils::streamFor($response->getBody());
        //         dd($stream->getContents());
        //         return json_decode($stream->getContents());
        //     });
        // }

        if($response->failed())
        {
            $response->throw(function ($response, $e){
                $stream = Utils::streamFor($response->getBody());
                //dd($stream->getContents());
                return json_decode($stream->getContents());
            });
        }
        return json_decode($response->body());
    }

    public function post(string $endpoint, array $params)
    {

        $response = Http::withHeaders($this->headers)
                    ->withOptions([
                        'debug' => false,
                    ])->post($this->url . $endpoint, $params);

        if($response->serverError())
        {
            $response->throw(function ($response, $e){
                $stream = Utils::streamFor($response->getBody());
                //dd($stream->getContents());
                return json_decode($stream->getContents());
            });
        }

        if($response->clientError())
        {
            $response->throw(function ($response, $e){
                $stream = Utils::streamFor($response->getBody());
                //dd($stream->getContents());
                return json_decode($stream->getContents());
            });
        }

        $response->throw()->json();

        return json_decode($response->body());
    }

    public function put(string $endpoint, array $params)
    {

        $response = Http::withHeaders($this->headers)
                    ->withOptions([
                        'debug' => false,
                    ])->put($this->url . $endpoint, $params);

        if($response->serverError())
        {
            $response->throw(function ($response, $e){
                $stream = Utils::streamFor($response->getBody());
                //dd($stream->getContents());
                return json_decode($stream->getContents());
            });
        }

        if($response->clientError())
        {
            $response->throw(function ($response, $e){
                $stream = Utils::streamFor($response->getBody());
                //dd($stream->getContents());
                return json_decode($stream->getContents());
            });
        }

        $response->throw()->json();

        return json_decode($response->body());
    }
}
