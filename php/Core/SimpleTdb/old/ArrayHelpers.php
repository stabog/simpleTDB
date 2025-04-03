<?php

namespace SimpleTdb;

class ArrayHelpers
{
    public static function arrayToString(array $array, array $separators, $delBreaks = true): string
    {
        $string = '';

        if (count($array) == 0) return $string;

        $recurse = function ($items, $curLev) use ($separators, $delBreaks, &$recurse) {
            if (count($items) == 0) return '';

            $last_id = max(array_keys($items));

            $elem = [];
            for ($id = 0; $id <= $last_id; $id++) {
                if (!isset($items[$id])) {
                    $elem[$id] = '';
                } else if (is_array($items[$id])) {
                    $nextLev = $curLev + 1;
                    $elem[$id] = $recurse($items[$id], $nextLev);
                } else {
                    if ($delBreaks) $items[$id] = preg_replace("/\n|\r\n/", '<sb>', $items[$id]);
                    $elem[$id] = $items[$id];
                }
            }

            $elem[] = '';
            $separ = $separators[$curLev];
            return implode($separ, $elem);
        };

        $string = $recurse($array, 0);

        return $string;
    }

    public static function stringToArray(string $string, array $separators): array
    {
        if (strlen($string) == 0 || trim($string) === '') return [];

        $recurs = function ($string, $curLev) use ($separators, &$recurs) {
            $separ = $separators[$curLev];
            $nextLev =  $curLev + 1;

            $items = explode($separ, $string);
            if (count($items) == 0) {
                return [$string];
            }

            if (end($items) == null || trim(end($items)) === '') array_pop($items);

            foreach ($items as $elem) {
                if (isset($separators[$nextLev]) and strpos($elem, $separators[$nextLev]) !== false) {
                    $elem = $recurs($elem, $nextLev);
                } else {
                    $elem = preg_replace("/<sb>/", "\n", $elem);
                }

                $array[] = $elem;
            }

            return $array;
        };

        return $recurs($string, 0);
    }
}
