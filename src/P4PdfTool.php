<?php

namespace P4Pdf;

/**
 * P4Pdf Tool utility class
 * 
 * Provides helper methods for cryptographic operations.
 *
 * @package P4Pdf
 */
class P4PdfTool
{
    /**
     * Generate a random string using SHA1 hashing.
     *
     * @param int $length Desired length of the random string
     * @return string Random string
     */
    public static function rand_sha1(int $length): string
    {
        $max = ceil($length / 40);
        $random = '';
        
        for ($i = 0; $i < $max; $i++) {
            $random .= sha1(microtime(true) . mt_rand(10000, 90000));
        }
        
        return substr($random, 0, $length);
    }

    /**
     * Generate a random string using MD5 hashing.
     *
     * @param int $length Desired length of the random string
     * @return string Random string
     */
    public static function rand_md5(int $length): string
    {
        $max = ceil($length / 32);
        $random = '';
        
        for ($i = 0; $i < $max; $i++) {
            $random .= md5(microtime(true) . mt_rand(10000, 90000));
        }
        
        return substr($random, 0, $length);
    }
}
