<?php

namespace App\Helpers;

use File;
use Illuminate\Support\Str;

class ArmaScript
{
    /**
     * Extracts the addons from the SQF files classnames.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function addons($source, $flagged = [])
    {
        $usedAddons = collect();
        $files = File::allFiles($source);

        foreach ($files as $file) {
            $file = (string) $file;

            if (!Str::endsWith(strtolower($file), 'sqf')) {
                continue;
            }

            $lines = explode('\n', file_get_contents($file));

            foreach ($lines as $line) {
                if (!Str::startsWith(trim($line), 'comment')) {
                    $matches = [];
                    preg_match('/"([\S]+)"/', $line, $matches);
                    $classname = $matches[1];

                    foreach ($flagged as $flag) {
                        if (Str::startsWith(strtolower($classname), strtolower($flag))) {
                            $usedAddons->push($flag);
                        }
                    }
                }
            }
        }

        return $usedAddons->unique();
    }

    /**
     * Checks the given file for SQF syntax errors.
     * Returns any error messages, or empty string.
     *
     * @return string
     */
    public static function check($source)
    {
        if (!is_dir($source)) {
            return shell_exec('python ' . resource_path('utils/sqf_validator.py') . ' ' . $source);
        } else {
            $files = File::allFiles($source);

            foreach ($files as $file) {
                $file = (string) $file;

                if (!Str::endsWith(strtolower($file), 'sqf')) {
                    continue;
                }

                $display_name = substr($file, strlen($source));
                $output = "{$display_name}: ";
                $result = shell_exec('python ' . resource_path('utils/sqf_validator.py') . ' ' . $file);
                $lines = explode('\n', $result);
                $lines = implode(', ', $lines);
                $output = $output . $lines;

                if (strlen(trim($lines)) != 0) {
                    return $output;
                }
            }
        }
    }
}
