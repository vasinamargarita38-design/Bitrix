<?php
namespace Yandex\Disk;

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
