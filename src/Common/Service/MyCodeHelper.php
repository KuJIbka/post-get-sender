<?php

namespace Common\Service;


class MyCodeHelper
{
    private static $instance;
    private function __construct()
    {
    }

    public static function get()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function parseString($stringValue)
    {
        $hashes = [];
        while (preg_match("/^((base64)|(md2)|(md4)|(md5)|(sha1)|(sha224)|(sha256)|(sha384)|(sha512)|(ripemd128)|(ripemd160)|(ripemd256)|(ripemd320)|(whirlpool)|(tiger128,3)|(tiger160,3)|(tiger192,3)|(tiger128,4)|(tiger160,4)|(tiger192,4)|(snefru)|(snefru256)|(gost)|(adler32)|(crc32)|(crc32b)|(fnv132)|(fnv164)|(joaat)|(haval128,3)|(haval160,3)|(haval192,3)|(haval224,3)|(haval256,3)|(haval128,4)|(haval160,4)|(haval192,4)|(haval224,4)|(haval256,4)|(haval128,5)|(haval160,5)|(haval192,5)|(haval224,5)|(haval256,5))\(/", $stringValue, $hashMatch)) {
            $hashFunc = $hashMatch[1];
            $hashes[] = $hashFunc;
            $stringValue = preg_replace('/^'.$hashFunc.'\(/', '', $stringValue);
            $stringValue = mb_substr($stringValue, 0, mb_strlen($stringValue) - 1);
        }
        #var_dump($hashes, $stringValue);
        while (null !== ($func = array_pop($hashes))) {
            if ($func === 'base64') {
                $stringValue = base64_encode($stringValue);
            } else {
                $stringValue = hash($func, $stringValue);
            }
        }
        #exit();
        return $stringValue;
    }
}
