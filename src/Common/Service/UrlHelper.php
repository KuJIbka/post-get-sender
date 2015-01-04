<?php

namespace Common\Service;


class UrlHelper
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

    public function getFullUrl($urlText, $fromUrl = '')
    {
        $fullDomain = '';
        $urlWithoutQuery = '';
        if ($fromUrl) {
            if (preg_match("/https?:\/\/[^\/]+/i", $fromUrl, $urlMatch)) {
                $fullDomain = $urlMatch[0];
            }
            if (preg_match("/^[^\?]+/i", $fromUrl, $urlMatch)) {
                $urlWithoutQuery = rtrim($urlMatch[0], '/');
            }
        }

        if ($urlText[0] == '/') {
            if ($fromUrl) {
                $urlText = $fullDomain.$urlText;
            } else {
                $preText = 'http';
                if (isset($_SERVER['HTTPS'])) {
                    $preText .= 's';
                }
                $urlText = $preText.'://'.$_SERVER['HTTP_HOST'].$urlText;
            }
        # if begins from ./
        } elseif (preg_match('/^\.\//', $urlText)) {
            $urlText = $urlWithoutQuery.'/'.ltrim($urlText, './');
        # If begins from ../
        } elseif (preg_match('/^\.\.\//', $urlText)) {
            $lastSlash = strrpos($urlWithoutQuery, '/');
            if ($lastSlash !== false) {
                $urlWithoutQuery = substr($urlWithoutQuery, 0, $lastSlash);
            }
            $urlText = $urlWithoutQuery.'/'.ltrim($urlText, './');
        # if begins from letter
        } elseif (!preg_match('/^http/', $urlText)) {
            if ($fromUrl) {
                $urlText = $urlWithoutQuery.'/'.$urlText;
            } else {
                $urlText = 'http://'.$urlText;
            }
        }

        return $urlText;
    }
}
