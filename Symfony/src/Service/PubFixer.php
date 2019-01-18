<?php
declare(strict_types=1);

namespace App\Service;

class PubFixer{

    public function __construct()
    {
    }

    public function fix(string $string): string
    {

        $string = trim($string, "., \t\n\r\0\x0B");

        if (preg_match('/\d\d\d\d-\d\d-\d\d$/', $string)){
            $string = substr($string, 0, -10);
        }
        else if (preg_match('/\d\d\d\d\.$/', $string)){
            $string = substr($string, 0, -4);
        }
        else if (preg_match('/\d\d\d\d$/', $string)){
            $string = substr($string, 0, -4);
        }
        else if (preg_match('/\(\d\d\d\d\)$/', $string)){
            $string = substr($string, 0, -6);
        }

        if (preg_match('/^NY:/', $string)){
            $string = substr($string, 3);
        }
        else if (preg_match('/^New York:/', $string)){
            $string = substr($string, 9);
        }
        else if (preg_match('/^London:/', $string)){
            $string = substr($string, 7);
        }

        $string = trim($string, "., \t\n\r\0\x0B");

        return $string;
    }
}

