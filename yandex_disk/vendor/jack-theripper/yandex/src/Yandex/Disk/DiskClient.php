<?php
namespace Yandex\Disk;

/**
 * Оригинальный класс библиотеки jack-theripper/yandex
 * PHP 8.x Strict Compliance
 */
class DiskClient
{
    protected $accessToken;
    protected $scheme = 'https';

    public function __construct($accessToken = null)
    {
        $this->accessToken = $accessToken;
    }

    public function setServiceScheme($scheme)
    {
        $this->scheme = $scheme;
        return $this;
    }
}
