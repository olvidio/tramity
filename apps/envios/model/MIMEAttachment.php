<?php

namespace envios\model;

class MIMEAttachment extends MIMEContainer
{

    protected $content_type = "application/octet-stream";
    protected $content_enc = "base64";
    protected $filename;
    protected $content;

    function __construct($filename = "", $mimetype = "")
    {
        parent::__construct();

        if (!empty($filename)) {
            $this->set_file($filename, $mimetype);
        }

        $this->content = uniqid(rand(1, 1000));
    }

    public function set_file($filename, $mimetype = "")
    {

        $fr = fopen($filename, "rb");

        if (!$fr) {
            $classname = __CLASS__;
            trigger_error("[$classname] Couldn't open '$filename' to be attached",
                E_USER_NOTICE);
            return false;
        }

        if (!empty($mimetype)) {
            $this->content_type = $mimetype;
        }

        $buffer = fread($fr, filesize($filename));
        $this->content = base64_encode($buffer);
        $this->filename = $filename;
        unset($buffer);
        fclose($fr);

        return true;

    }

    public function get_file()
    {

        $retval = array('filename' => $this->filename,
            'mimetype' => $this->content_type);

        return $retval;
    }

    public function create()
    {

        if (!isset($this->content)) {
            return;
        }

        $addheaders = (is_array($this->add_header)) ? implode($this->add_header, "\r\n") : "";

        $headers = "Content-Type: {$this->content_type}; filename=$this->filename\r\n";
        $headers .= "Content-Transfer-Encoding: {$this->content_enc}\r\n";
        $headers .= "Content-Disposition: attachment\r\n$addheaders\r\n";
        //$headers .= chunk_split($this->content)."\r\n";
        //$headers .= $this->mb_chunk_split($this->content, 72, "\r\n");
        $headers .= chunk_split($this->content, 72, "\r\n");
        $headers .= " \r\n \r\n";

        return $headers;

    }


    public function setFilename($filename)
    {
        $this->filename = $filename;

        /*
        if( ($mime_type = mime_content_type($filename)) !== FALSE) {
            $this->set_content_type($mime_type);
        }
        */
    }


}
