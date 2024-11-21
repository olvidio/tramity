<?php

namespace synology\model;

use Kazio73\SynologyApiClient\Client\Client;

class ApiAuthTramity extends Client
{



    public const API_SERVICE_NAME = 'API';

    public const API_NAMESPACE = 'SYNO';

    /**
     * Info API setup
     *
     * @param string $address
     * @param int $port
     * @param string $protocol
     * @param int $version
     * @param boolean $verifySSL
     */
    public function __construct(
        $address,
        $port = null,
        $protocol = null,
        $version = 1,
        $verifySSL = false
    )
    {
        parent::__construct(
            self::API_SERVICE_NAME,
            self::API_NAMESPACE,
            $address,
            $port,
            $protocol,
            $version,
            $verifySSL
        );
    }

    public function getInfo(): array
    {
        return $this->request(self::API_SERVICE_NAME, 'Info', 'FileStation/info.cgi', 'getinfo');
    }



    public function rename(string $path, string $name): array
    {
        $path = $this->escapeParam($path);
        $name = $this->escapeParam($name);

        return $this->request(
            self::API_SERVICE_NAME,
            'Rename',
            'entry.cgi',
            'rename',
            [
                'path' => $path,
                'name' => $name,
            ]
        );
    }
}