<?php

namespace Fyr\PhilipsHue\Apis;

use Fyr\PhilipsHue\ApiClient;
use Fyr\PhilipsHue\Helpers\ColorConversion;

class Lights extends ApiClient
{
     protected $client;
     protected $prefix = '/lights';

     public function __construct($client)
     {
          $this->client = $client;
     }

     public function list($params = [])
     {
          return $this->client->get($this->prefix);
     }

     public function get($id)
     {
          return $this->client->get($this->prefix . '/' . $id);
     }

     public function toggle($id)
     {
          $light = $this->get($id);

          return $this->client->put($this->prefix . '/' . $id . '/state', [
               'on' => $light->state->on ? false : true
          ]);
     }

     public function on($id)
     {
          return $this->client->put($this->prefix . '/' . $id . '/state', [
               'on' => true
          ]);
     }

     public function off($id)
     {
          return $this->client->put($this->prefix . '/' . $id . '/state', [
               'on' => false
          ]);
     }

     public function setOnState($id, $state)
     {
          return $this->client->put($this->prefix . '/' . $id . '/state', [
               'on' => $state
          ]);
     }

     public function setColor($id, $color)
     {
          $light = $this->get($id);

          if (!$light) return false;

          if (!$light->state->on) {
               $this->on($id);
          }

          $xyb = ColorConversion::concertHexToXY($color);
          return $this->client->put($this->prefix . '/' . $id . '/state', [
               'xy' => [$xyb['x'], $xyb['y']],
               'bri' => $xyb['bri']
          ]);
     }
}
