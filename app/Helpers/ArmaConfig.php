<?php

namespace App\Helpers;

use Log;
use \stdClass;
use App\Helpers\ArmaConfigError;
use Illuminate\Support\Arr;

class ArmaConfig
{
    protected static $terminals = [
        '/^(class\s)/' => 'T_CLASS',
        '/^(\s+)/' => 'T_WHITESPACE',
        '/^([-0-9\+\.]+e*[-0-9\+\.]*)/' => 'T_NUMBER',
        '/^([A-Za-z_]+[0-9_]*[A-Za-z_]*)/' => 'T_PROP',
        '/^({)/' => 'T_BLOCKSTART',
        '/^(=)/' => 'T_ASSIGNMENT',
        '/^(;)/' => 'T_EOL',
        '/^(})/' => 'T_BLOCKEND',
        '/^("[\s\S]*?")/' => 'T_STRING',
        '/^(\[\s*\]\s*=)/' => 'T_ARRAY_START',
        '/^(,)/' => 'T_ARRAY_SEP',
        '/^({[\s\S]*})/' => 'T_ARRAY',
        '/^(#include[\s\S]+)/' => 'T_INCLUDE'
    ];

    public static function run($source, $filepath) {
        $tokens = [];
        $emptyLines = 0;

        foreach ($source as $number => $line) {
            $offset = 0;

            if (strlen(trim($line)) == 0) {
                $emptyLines++;
            } else {
                while ($offset < strlen($line)) {
                    $result = static::match($line, $number, $offset);

                    if ($result === false) {
                        $realLine = $number + $emptyLines;
                        $basename = basename($filepath);
                        $error = new ArmaConfigError();
                        $error->message = "Unable to parse line {$realLine} in {$basename}";
                        Log::error($error->message);
                        return $error;
                    }

                    $tokens[] = $result;
                    $offset += strlen($result['match']);
                }
            }
        }

        return static::compile(static::clean($tokens));
    }

    protected static function match($line, $number, $offset) {
        $string = substr($line, $offset);

        foreach (static::$terminals as $pattern => $name) {
            if (preg_match($pattern, $string, $matches)) {
                return [
                    'match' => $matches[1],
                    'token' => $name,
                    'line' => $number + 1
                ];
            }
        }

        return false;
    }

    protected static function seekerUp($seeker, $level = 1)
    {
        $result = $seeker;

        for ($i = 0; $i < $level; $i++) {
            $s = explode ('.', $result);
            array_pop($s);
            $result = implode('.', $s);
        }

        return $result;
    }

    protected static function cleanArray($items)
    {
        $result = [];

        foreach ($items as $i) {
            if (is_numeric($i)) {
                $result[] = (float)$i;
                continue;
            }

            if (is_string($i)) {
                if ($i == 'true') {
                    $result[] = true;
                    continue;
                }

                if ($i == 'false') {
                    $result[] = false;
                    continue;
                }

                $result[] = str_replace ('"', '', $i);
                continue;
            }

            $result[] = $i;
        }

        return $result;
    }

    protected static function compile($tokens)
    {
        $object = [];
        $isClass = false;
        $hasAssignment = false;
        $seeker = '';

        foreach ($tokens as $t) {
            $token = $t['token'];
            $match = $t['match'];
            $value = Arr::get($t, 'value', $match);

            if ($token == 'T_BLOCKEND') {
                $seeker = explode ('.', $seeker);
                array_pop($seeker);
                $seeker = implode('.', $seeker);
                continue;
            }

            if ($token == 'T_CLASSNAME') {
                $seeker = ($seeker == '') ? $match : $seeker . '.' . $match;
                Arr::set($object, $seeker, []);
                continue;
            }

            if ($token == 'T_ARRAY') {
                $seeker = ($seeker == '') ? $match : $seeker . '.' . $match;
                Arr::set($object, $seeker, static::cleanArray($value));
                $hasAssignment = false;
                $seeker = explode ('.', $seeker);
                array_pop($seeker);
                $seeker = implode('.', $seeker);
                continue;
            }

            if ($hasAssignment && in_array($token, ['T_STRING', 'T_PROP', 'T_NUMBER'])) {
                Arr::set($object, $seeker, $value);
                $hasAssignment = false;
                $seeker = explode ('.', $seeker);
                array_pop($seeker);
                $seeker = implode('.', $seeker);
                continue;
            }

            if ($token == 'T_PROP' && !$hasAssignment) {
                $seeker = ($seeker == '') ? $match : $seeker . '.' . $match;
                Arr::set($object, $seeker, []);
                $hasAssignment = true;
                continue;
            }
        }

        return $object;
    }

    protected static function clean($tokens) {
        $cleaned = [];
        $arrayStart = false;
        $arrayTemp = [];
        $arrayTempName = '';
        $arrayTempStartFilling = false;
        $prevMatch = '';
        $prevIndex = 0;
        $nextIsClassname = false;
        $nestedArray = [];
        $isNested = false;

        foreach ($tokens as $t) {
            if ($arrayStart) {
                if ($t['token'] == 'T_ARRAY_SEP') {
                    continue;
                }

                if ($t['token'] == 'T_BLOCKSTART' && !$arrayTempStartFilling) {
                    $arrayTempStartFilling = true;
                    continue;
                }

                if ($t['token'] == 'T_BLOCKSTART' && $arrayTempStartFilling) {
                    $isNested = true;
                    continue;
                }

                if ($t['token'] == 'T_BLOCKEND') {
                    if ($isNested) {
                        array_push($arrayTemp, static::cleanArray($nestedArray));
                        $nestedArray = [];
                        $isNested = false;
                        continue;
                    } else {
                        $cleaned[$prevIndex]['token'] = 'T_ARRAY';
                        $cleaned[$prevIndex]['value'] = $arrayTemp;
                        $cleaned[$prevIndex]['match'] = $arrayTempName;
                        $arrayTempStartFilling = false;
                        $arrayStart = false;
                        $arrayTempName = '';
                        $arrayTemp = [];
                        continue;
                    }
                }

                if ($arrayTempStartFilling && $t['token'] != 'T_WHITESPACE') {
                    if ($isNested) {
                        array_push($nestedArray, $t['match']);
                        continue;
                    } else {
                        array_push($arrayTemp, $t['match']);
                        continue;
                    }
                }

                continue;
            }

            if ($nextIsClassname && $t['token'] == 'T_PROP') {
                $t['token'] = 'T_CLASSNAME';
                $cleaned[] = $t;
                $nextIsClassname = false;
                continue;
            }

            if ($t['token'] == 'T_CLASS') {
                $nextIsClassname = true;
                $cleaned[] = $t;
                continue;
            }

            if ($t['token'] == 'T_NUMBER') {
                $t['value'] = (float)str_replace ([';', ',', '}', '{'], '', $t['match']);
                $cleaned[] = $t;
                continue;
            }

            if ($t['token'] == 'T_STRING') {
                $t['value'] = str_replace ('"', '', $t['match']);
                $cleaned[] = $t;
                continue;
            }

            if ($t['token'] == 'T_ARRAY_START') {
                $arrayStart = true;
                $arrayTempName = $prevMatch;
                continue;
            }

            $cleaned[] = $t;
            $prevIndex = count($cleaned) - 1;
            $prevMatch = $t['match'];
        }

        return $cleaned;
    }

    protected static function arrayToObject($array)
    {
        $obj = new stdClass;

        foreach($array as $k => $v) {
            if (strlen($k)) {
                if (is_array($v)) {
                    $obj->{strtolower($k)} = static::arrayToObject($v);
                } else {
                    $obj->{strtolower($k)} = $v;
                }
            }
        }

        return $obj;
    }

    public static function convert($file)
    {
        if (!file_exists($file)) {
            abort(403, 'File ' . $file . ' does not exist.');
            return;
        }

        $contents = file_get_contents($file);
        $contents = preg_replace('#^\s*//.+$#m', '', $contents); // Remove single line comments
        $contents = preg_replace('!/\*.*?\*/!s', '', $contents); // Remove multi line comments
        $lines = explode(PHP_EOL, $contents);

        $result = static::run($lines, $file);

        if ($result instanceof ArmaConfigError) {
            return $result;
        }

        return static::arrayToObject($result);
    }
}
