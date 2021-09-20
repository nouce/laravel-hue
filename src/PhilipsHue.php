<?php

namespace Fyr\PhilipsHue;

use Fyr\PhilipsHue\ApiClient;

class PhilipsHue
{
    protected static $user;
    public function __construct($user)
    {
        self::$user = $user;
    }
    public static function api()
    {
        return new ApiClient(self::$user);
    }
}
