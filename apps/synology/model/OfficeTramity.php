<?php

namespace synology\model;

use Kazio73\SynologyApiClient\Client\Client;

class OfficeTramity extends Client
{


    public const API_SERVICE_NAME = 'Office';

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
        $version = 1;
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
        return $this->request(
            self::API_SERVICE_NAME,
            'Info',
            'entry.cgi',
            'get'
        );
    }


    /*
     * api: SYNO.Office.Export
     * method: download
     * version:1
     * password: ""
     * path: "link:10tqPWmvrJSNxZ6agqLx3mHJlmm9jXIU"
     * format: "ms"
     * SynoToken:  l00m1iZIuGAvw
     */
    public function export(string $path_link): string
    {
        $export_end_point = 'prova.docx';
        $end_point = '"entry.cgi/'.$export_end_point.'"';
        $path_link = $this->escapeParam($path_link);

        return $this->request(
            self::API_SERVICE_NAME,
            'Export',
            $end_point,
            'download',
            [
                'path' => $path_link,
                'format' => 'ms',
            ]
        );
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