<?php

namespace envios\model;

class MIMESubcontainer extends MIMEContainer
{

    public function create()
    {
        $addheaders = (is_array($this->add_header)) ?
            implode($this->add_header, "\r\n") : "";
        $headers = "Content-Type: {$this->content_type}; boundary=" .
            "{$this->boundary}\r\n";
        $headers .= "Content-Transfer-Encoding: {$this->content_enc}" .
            "\r\n$addheaders\r\n";

        if (is_array($this->subcontainers)) {
            foreach ($this->subcontainers as $val) {
                $headers .= "--{$this->boundary}\r\n";
                $headers .= $val->create();
            }
            $headers .= "\r\n--{$this->boundary}--\r\n";
        }
        return $headers;
    }
}