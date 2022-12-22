<?php

namespace Fyr\PhilipsHue;

use Illuminate\Support\Facades\Http;
use GuzzleHttp\Psr7\Utils;

use Fyr\PhilipsHue\Apis\Lights;

class ApiClient
{
     public $apiRequest;
     public static $user;

     public function __construct($user)
     {
          self::$user = $user;

          if (!$this->checkAccessToken()) {
               $this->refreshAccessToken();
          }

          if (!self::$user->philips_hue_username) {
               $this->generateUsername();
          }

          $this->apiRequest = new ApiRequest(self::$user);
     }

     public function hasValidAccessToken()
     {
          return $this->checkAccessToken();
     }

     protected function checkAccessToken()
     {
          $response = Http::withHeaders([
               'Authorization' => 'Bearer ' . self::$user->philips_hue_access_token
          ])->put('https://api.meethue.com/bridge/0/config', [
               'linkbutton' => true
          ]);

          $jsonResponse = $response->json();
          ray($jsonResponse);
          if (isset($jsonResponse['fault']['detail'])) {
               if ($jsonResponse['fault']['detail']['errorcode'] == 'keymanagement.service.access_token_expired') {
                    return false;
               }
          }

          return true;
     }

     protected function refreshAccessToken()
     {
          $digestHeaderResponse = Http::asForm()->post('https://api.meethue.com/v2/oauth2/token?grant_type=refresh_token');

          $realm = 'oauth2_client@api.meethue.com';
          $digest = explode(',', $digestHeaderResponse->header('WWW-Authenticate'));
          $nonce = str_replace('"', '', str_replace('nonce="', '', $digest[1]));
          $hash1 = md5(env('HUE_CLIENT_ID') . ':' . $realm . ':' . env('HUE_CLIENT_SECRET'));
          $hash2 = md5('POST:/v2/oauth2/token');
          $calculatedResponse = md5($hash1 . ':' . $nonce . ':' . $hash2);

          //dd('Digest username="' . env("HUE_CLIENT_ID") . '", realm="oauth2_client@api.meethue.com", nonce="' . $nonce . '", uri="/v2/oauth2/token", response="' . $calculatedResponse . '"');

          $response = Http::withHeaders([
                    'Authorization' => 'Digest username="' . env("HUE_CLIENT_ID") . '", realm="oauth2_client@api.meethue.com", nonce="' . $nonce . '", uri="/v2/oauth2/token", response="' . $calculatedResponse . '"'
               ])
               ->asForm()->post('https://api.meethue.com/v2/oauth2/token', [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => self::$user->philips_hue_refresh_token
               ]);

          $data = $response->object();
          ray($data);
          if (isset($data->access_token)) {
               self::$user->update([
                    'philips_hue_access_token' => $data->access_token,
                    'philips_hue_refresh_token' => $data->refresh_token
               ]);
          }
     }

     private function generateUsername()
     {
          $bridgeResponse = Http::withHeaders([
               'Authorization' => 'Bearer ' . self::$user->philips_hue_access_token
          ])->put('https://api.meethue.com/bridge/0/config', [
               'linkbutton' => true
          ]);

          if ($bridgeResponse->failed()) {
               return false;
          }

          $usernameResponse = Http::withHeaders([
               'Authorization' => 'Bearer ' . self::$user->philips_hue_access_token
          ])->post('https://api.meethue.com/bridge', [
               'devicetype' => 'fyr'
          ]);

          $data = $usernameResponse->json();

          self::$user->update([
               'philips_hue_username' => $data[0]['success']['username']
          ]);
     }

     // Endpoints

     public function lights()
     {
          if (!$this->checkAccessToken()) {
               $this->refreshAccessToken();
          }
          return new Lights($this->apiRequest);
     }
}
