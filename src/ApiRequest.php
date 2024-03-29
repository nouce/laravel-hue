<?php

namespace Fyr\PhilipsHue;

use Illuminate\Support\Facades\Http;
use GuzzleHttp\Psr7\Utils;


class ApiRequest
{
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

     public function get(string $endpoint, array $params = [])
     {
          $response = Http::withHeaders($this->headers)->get($this->url . $endpoint, $params);

          if ($response->failed()) {
               $stream = Utils::streamFor($response->getBody());
               $details = json_decode($stream->getContents());

               return $details;
          }
          return json_decode($response->body());
     }

     public function post(string $endpoint, array $params)
     {

          $response = Http::withHeaders($this->headers)
               ->withOptions([
                    'debug' => false,
               ])->post($this->url . $endpoint, $params);

          if ($response->failed()) {
               $stream = Utils::streamFor($response->getBody());
               $details = json_decode($stream->getContents());

               return $details;
          }

          return json_decode($response->body());
     }

     public function put(string $endpoint, array $params)
     {

          $response = Http::withHeaders($this->headers)
               ->withOptions([
                    'debug' => false,
               ])->put($this->url . $endpoint, $params);

          if ($response->failed()) {
               $stream = Utils::streamFor($response->getBody());
               $details = json_decode($stream->getContents());

               return $details;
          }

          return json_decode($response->body());
     }
}
