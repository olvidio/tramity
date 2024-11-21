<?php

namespace synology\model;

use Kazio73\SynologyApiClient\Client\Client;
use Kazio73\SynologyApiClient\Client\DriveStationClient;
use Kazio73\SynologyApiClient\Client\SynologyException;

/**
 * Class Client.
 */
class SynologyDriveClientTramity extends DriveStationClient
{

    public function ponerCoockie()
    {

    }

    public function list( $path = '' ): array
    {
        $path = $this->escapeParam($path);

        return $this->request(
            self::API_SERVICE_NAME."Files",
            'list',
            'entry.cgi',
            'start',
            [
                "path" => $path,
            ]
        );
    }

}