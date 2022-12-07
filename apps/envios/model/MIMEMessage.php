<?php

namespace envios\model;

class MIMEMessage extends MIMEContainer
{

    public function create()
    {


        $addheaders = (is_array($this->add_header)) ? implode("\r\n", $this->add_header) : '';

        $headers = "Content-Type: $this->content_type\r\n";
        $headers .= "Content-Transfer-Encoding: $this->content_enc\r\n$addheaders\r\n";
        $headers .= $this->content . "\r\n";

        return $headers;

    }

}
