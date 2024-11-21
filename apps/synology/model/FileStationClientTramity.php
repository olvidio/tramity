<?php

namespace synology\model;

use Kazio73\SynologyApiClient\Client\FileStationClient;
use Mpdf\Tag\S;

class FileStationClientTramity extends FileStationClient
{

    public function BackgroundTask(
        $offset = 0,
        $limit = 0,
        $sort_by = 'crtime',
        $sort_direction = 'asc',
        $api_filter = ''
    ): array
    {
        return $this->request(
            self::API_SERVICE_NAME,
            'BackgroundTask',
            'entry.cgi',
            'list',
            [
                "offset"  => $offset,
                "limit" => $limit,
                "sort_by" => $sort_by,
                "sort_direction" => $sort_direction,
                "api_filter" => $api_filter,
            ]
        );
    }

    /*
     * OJO!! valor true en texto
     */
    public function CopyMove(
        $path = '',
        $dest_folder_path = '',
        $overwrite = 'true',
        $remove_src = 'false',
        $accurate_progress = 'true',
        $search_taskid = ''
    ): array
    {
        $path = $this->escapeParam($path);
        $dest_folder_path = $this->escapeParam($dest_folder_path);

        return $this->request(
            self::API_SERVICE_NAME,
            'CopyMove',
            'entry.cgi',
            'start',
            [
                "path" => $path,
                "dest_folder_path" => $dest_folder_path,
                "overwrite" => $overwrite,
                "remove_src" => $remove_src,
                "accurate_progress" => $accurate_progress,
                "search_taskid" => $search_taskid,
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

    /*
        offset
        limit
        sort_by
        sort_direction
        force_clean
     */
    public function listLinks(
        $offset = '',
        $limit = '',
        $sort_by = '',
        $sort_direction = '',
        $force_clean = ''
    ): array
    {

        return $this->request(
            self::API_SERVICE_NAME,
            'Sharing',
            'entry.cgi',
            'list',
            [
                "offset" => $offset,
                "limit" => $limit,
                "sort_by" => $sort_by,
                "sort_direction" => $sort_direction,
                "force_clean" => $force_clean,
            ]
        );
    }


    /*
    path
password
date_expired
date_available
    */

    public function createLink(
    $path = '',
    $password = '',
    $date_expired = '',
    $date_available = ''
    ): array
    {

        return $this->request(
            self::API_SERVICE_NAME,
            'Sharing',
            'entry.cgi',
            'create',
            [
                "path" =>$path,
                "password" =>$password,
                "date_expired" =>$date_expired,
                "date_available" => $date_available,
            ]
        );
    }






}