<?php
declare(strict_types=1);

namespace App\Service;

use SimpleXMLElement;

class IsbnConverter{

    public function isbn10to13(string $isbn): string
    {
        $isbn = str_replace('-', '', $isbn);
        $isbn = trim($isbn);
        $isbn2 = substr("978" . trim($isbn), 0, -1);
        $sum13 = $this->genchksum13($isbn2);

        return "$isbn2$sum13";
    }

    public function isbnFromSxe(SimpleXMLElement $sxe): ?string
    {
        $isbn = null;

        if (isset($sxe->ItemAttributes->EAN)){
            $isbn = (string)$sxe->ItemAttributes->EAN;
        }
        else if (isset($sxe->ItemAttributes->ISBN)){
            $isbn = (string)$sxe->ItemAttributes->ISBN;
            if (13 != strlen($isbn)){
                $isbn = $this->isbn10to13($isbn);
            }
        }
        else if (isset($sxe->ItemAttributes->EISBN)){
            $isbn = (string)$sxe->ItemAttributes->EISBN;
            if (13 != strlen($isbn)){
                $isbn = $this->isbn10to13($isbn);
            }
        }

        if (null !== $isbn) {
            if (!preg_match('/^[\d]{13}$/', $isbn)) {
                return null;
            }
        }
        return $isbn;
    }

    public function isbn13to10(string $isbn): string
    {
        $isbn = str_replace('-', '', $isbn);
        if (preg_match('/^(\d{3})(\d{9})\d$/', $isbn, $m)) {
            if ('978' != $m[1]){
                die ("bad isbn $isbn\n");
            }

            $sequence = $m[2];
            $sum = 0;
            $mul = 10;
            for ($i = 0; $i < 9; $i++) {
                $sum = $sum + ($mul * (int) $sequence{$i});
                $mul--;
            }
            $mod = 11 - ($sum%11);
            if ($mod == 10) {
                $mod = "X";
            }
            else if ($mod == 11) {
                $mod = 0;
            }
            $isbn = $sequence.$mod;
        }
        return $isbn;
    }

    private function genchksum13(string $isbn): int
    {
        $isbn = trim($isbn);
        $tb = 0;
        for ($i = 0; $i <= 12; $i++) {
            $tc       = (int)substr($isbn, -1, 1);
            $isbn     = substr($isbn, 0, -1);

            if (false == $isbn){
                $isbn = "0";
            }
            $ta       = ($tc*3);

            $tci      = (int)substr($isbn, -1, 1);
            $isbn     = substr($isbn, 0, -1);
            $tb       = $tb + $ta + $tci;
        }

        $tg      = ($tb / 10);
        $tint    = intval($tg);

        if ($tint == $tg) {
            return 0;
        }

        $ts = (int)substr((string)$tg, -1, 1);
        $tsum = (10 - $ts);

        return $tsum;
    }
}

