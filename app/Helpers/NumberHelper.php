<?php

namespace App\Helpers;

class NumberHelper
{
    /**
     * Convertit un nombre en mots français
     */
    public static function numberToWords($number) {
        $number = (int) $number;
        
        if ($number == 0) {
            return 'zéro';
        }
        
        $units = [
            '', 'un', 'deux', 'trois', 'quatre', 'cinq', 'six', 'sept', 'huit', 'neuf',
            'dix', 'onze', 'douze', 'treize', 'quatorze', 'quinze', 'seize', 'dix-sept',
            'dix-huit', 'dix-neuf'
        ];
        
        $tens = [
            '', '', 'vingt', 'trente', 'quarante', 'cinquante', 'soixante',
            'soixante-dix', 'quatre-vingt', 'quatre-vingt-dix'
        ];
        
        $scales = [
            '', 'mille', 'million', 'milliard', 'billion'
        ];
        
        $convertGroup = function($num) use ($units, $tens) {
            $result = '';
            
            // Centaines
            if ($num >= 100) {
                $hundreds = intval($num / 100);
                if ($hundreds == 1) {
                    $result .= 'cent';
                } else {
                    $result .= $units[$hundreds] . ' cent';
                }
                if ($num % 100 == 0 && $hundreds > 1) {
                    $result .= 's';
                }
                $num %= 100;
                if ($num > 0) {
                    $result .= ' ';
                }
            }
            
            // Dizaines et unités
            if ($num >= 20) {
                $tensDigit = intval($num / 10);
                $unitsDigit = $num % 10;
                
                if ($tensDigit == 7 || $tensDigit == 9) {
                    $result .= $tens[$tensDigit - 1];
                    if ($unitsDigit == 1 && $tensDigit == 7) {
                        $result .= ' et onze';
                    } elseif ($unitsDigit == 1 && $tensDigit == 9) {
                        $result .= ' et onze';
                    } else {
                        $result .= ' ' . $units[10 + $unitsDigit];
                    }
                } else {
                    $result .= $tens[$tensDigit];
                    if ($unitsDigit == 1 && $tensDigit > 1) {
                        $result .= ' et un';
                    } elseif ($unitsDigit > 0) {
                        $result .= ' ' . $units[$unitsDigit];
                    }
                    if ($tensDigit == 8 && $unitsDigit == 0) {
                        $result .= 's';
                    }
                }
            } elseif ($num > 0) {
                $result .= $units[$num];
            }
            
            return $result;
        };
        
        $result = '';
        $scaleIndex = 0;
        
        while ($number > 0) {
            $group = $number % 1000;
            
            if ($group > 0) {
                $groupWords = $convertGroup($group);
                
                if ($scaleIndex == 1 && $group == 1) {
                    // "mille" au lieu de "un mille"
                    $groupWords = '';
                }
                
                if ($scaleIndex > 0) {
                    if ($scaleIndex == 1) {
                        $groupWords .= ' ' . $scales[$scaleIndex];
                    } else {
                        $groupWords .= ' ' . $scales[$scaleIndex];
                        if ($group > 1) {
                            $groupWords .= 's';
                        }
                    }
                }
                
                if ($result == '') {
                    $result = $groupWords;
                } else {
                    $result = $groupWords . ' ' . $result;
                }
            }
            
            $number = intval($number / 1000);
            $scaleIndex++;
        }
        
        return trim($result);
    }

    /**
     * Convertit un montant en francs CFA en lettres
     */
    public static function amountToWords($amount) {
        $amount = (float) $amount;
        $integerPart = (int) $amount;
        $decimalPart = round(($amount - $integerPart) * 100);
        
        $result = self::numberToWords($integerPart);
        
        if ($integerPart <= 1) {
            $result .= ' franc CFA';
        } else {
            $result .= ' francs CFA';
        }
        
        if ($decimalPart > 0) {
            $result .= ' et ' . self::numberToWords($decimalPart);
            if ($decimalPart <= 1) {
                $result .= ' centime';
            } else {
                $result .= ' centimes';
            }
        }
        
        return $result;
    }
}