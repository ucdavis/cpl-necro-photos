<?php

namespace App\Services;

class PhotoFilenameService
{
    /**
     * Generate a unique filename for a photo based on accession info.
     *
     * Example result: 0047-25x-a.jpg
     *
     * @param string $dir       Target directory to scan for existing files
     * @param string $cplNum    CPL number (will be padded to 4 digits)
     * @param string $year      Year (4- or 2-digit; only last 2 are used)
     * @param string $suffix    Accession suffix (may be empty)
     * @param string $extension File extension without dot, e.g. "jpg"
     */
    public static function generateFilename(
        string $dir,
        string $cplNum,
        string $year,
        string $suffix,
        string $extension
    ): string {
        // Pad CPL number to 4 digits with leading zeros
        $paddedCplNum = str_pad($cplNum, 4, '0', STR_PAD_LEFT);
        $yearTwoDigit = substr($year, -2);

        $base = sprintf('%s-%s%s', $paddedCplNum, $yearTwoDigit, $suffix ?: '');
        $escapedBase = preg_quote($base, '/');

        $existing = [];
        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $f) {
                if (preg_match('/^' . $escapedBase . '(?:-([a-z]+))?\.[^\.]+$/i', $f, $m)) {
                    $existing[] = isset($m[1]) ? strtolower($m[1]) : '';
                }
            }
        }

        // Start with -a and increment until an unused alphabetic code is found
        $candidate = 'a';
        while (in_array($candidate, $existing, true)) {
            $candidate = self::incrementAlpha($candidate);
        }

        return $base . '-' . $candidate . '.' . $extension;
    }

    /**
     * Increment alphabetic sequence: a -> b, z -> aa, az -> ba, etc.
     */
    private static function incrementAlpha(string $s): string
    {
        $chars = str_split($s);
        $i = count($chars) - 1;
        while ($i >= 0) {
            if ($chars[$i] !== 'z') {
                $chars[$i] = chr(ord($chars[$i]) + 1);
                for ($j = $i + 1; $j < count($chars); $j++) {
                    $chars[$j] = 'a';
                }
                return implode('', $chars);
            }
            $i--;
        }
        // all z's -> prepend 'a'
        return str_repeat('a', count($chars) + 1);
    }
}
