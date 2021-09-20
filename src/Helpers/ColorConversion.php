<?php
namespace Fyr\PhilipsHue\Helpers;

class ColorConversion
{
    public static function concertHexToXY($hex)
    {
        $rgb = self::hexToRGB($hex);
        return self::convertRGBToXY($rgb['r'], $rgb['g'], $rgb['b']);
    }

    public static function convertRGBToXY($red, $green, $blue)
    {
        // Normalize the values to 1
        $normalizedToOne['red'] = $red / 255;
        $normalizedToOne['green'] = $green / 255;
        $normalizedToOne['blue'] = $blue / 255;

        // Make colors more vivid
        foreach ($normalizedToOne as $key => $normalized) {
            if ($normalized > 0.04045) {
                $color[$key] = pow(($normalized + 0.055) / (1.0 + 0.055), 2.4);
            } else {
                $color[$key] = $normalized / 12.92;
            }
        }

        // Convert to XYZ using the Wide RGB D65 formula
        $xyz['x'] = $color['red'] * 0.664511 + $color['green'] * 0.154324 + $color['blue'] * 0.162028;
        $xyz['y'] = $color['red'] * 0.283881 + $color['green'] * 0.668433 + $color['blue'] * 0.047685;
        $xyz['z'] = $color['red'] * 0.000000 + $color['green'] * 0.072310 + $color['blue'] * 0.986039;

        // Calculate the x/y values
        if (array_sum($xyz) == 0) {
            $x = 0;
            $y = 0;
        } else {
            $x = $xyz['x'] / array_sum($xyz);
            $y = $xyz['y'] / array_sum($xyz);
        }

        return array(
            'x'   => $x,
            'y'   => $y,
            'bri' => round($xyz['y'] * 255)
        );
    }

    public static function convertXYToRGB($x, $y, $bri = 255)
    {
        // Calculate XYZ
        $z = 1.0 - $x - $y;
        $xyz['y'] = $bri / 255;
        $xyz['x'] = ($xyz['y'] / $y) * $x;
        $xyz['z'] = ($xyz['y'] / $y) * $z;

        // Convert to RGB using Wide RGB D65 conversion
        $color['red'] = $xyz['x'] * 1.656492 - $xyz['y'] * 0.354851 - $xyz['z'] * 0.255038;
        $color['green'] = -$xyz['x'] * 0.707196 + $xyz['y'] * 1.655397 + $xyz['z'] * 0.036152;
        $color['blue'] = $xyz['x'] * 0.051713 - $xyz['y'] * 0.121364 + $xyz['z'] * 1.011530;

        foreach ($color as $key => $normalized) {
            // Apply reverse gamma correction
            if ($normalized <= 0.0031308) {
                $color[$key] = 12.92 * $normalized;
            } else {
                $color[$key] = (1.0 + 0.055) * pow($normalized, 1.0 / 2.4) - 0.055;
            }

            // Scale back from a maximum of 1 to a maximum of 255
            $color[$key] = round($color[$key] * 255);
        }

        return $color;
    }

    public static function hexToRGB($hex, $alpha = false)
    {
        $hex      = str_replace('#', '', $hex);
        $length   = strlen($hex);
        $rgb['r'] = hexdec($length == 6 ? substr($hex, 0, 2) : ($length == 3 ? str_repeat(substr($hex, 0, 1), 2) : 0));
        $rgb['g'] = hexdec($length == 6 ? substr($hex, 2, 2) : ($length == 3 ? str_repeat(substr($hex, 1, 1), 2) : 0));
        $rgb['b'] = hexdec($length == 6 ? substr($hex, 4, 2) : ($length == 3 ? str_repeat(substr($hex, 2, 1), 2) : 0));
        if ( $alpha ) {
           $rgb['a'] = $alpha;
        }
        return $rgb;
    }
}