<?php

namespace envios\model;


class MIMEContainer
{

    protected $content_type = "text/plain";
    protected $content_enc = "7-bit";
    protected $content;
    protected $subcontainers;
    protected $boundary;
    protected $created;
    protected $add_header;

    function __construct()
    {
        $this->created = false;
        $this->boundary = uniqid(rand(1, 10000));
    }

    public function sendmail($to, $from, $subject, $add_headers = "")
    {
        mail($to, $subject, $this->get_message($add_headers),
            "From: $from\r\n");
    }

    public function get_message($add_headers = "")
    {
        return $this->create($add_headers);
    }

    public function create()
    {

        /* Standard Headers that exist on every MIME e-mail */
        $headers = "MIME-Version: 1.0\r\n" .
            "Content-Transfer-Encoding: {$this->content_enc}\r\n";

        $addheaders = (is_array($this->add_header)) ?
            implode($this->add_header, "\r\n") : '';

        /* If there is a subcontainer */
        if (is_array($this->subcontainers) &&
            (count($this->subcontainers) > 0)) {

            $headers .= "Content-Type: {$this->content_type}; charset=UTF-8; " .
                "boundary={$this->boundary}\r\n$addheaders\r\n\r\n";

            //$headers .= wordwrap("If you are reading this portion of the e-mail," .
            //		"then you are not reading this e-mail through a" .
            //		" MIME compatible e-mail client\r\n\r\n");

            foreach ($this->subcontainers as $val) {
                if (method_exists($val, "create")) {
                    $headers .= "--{$this->boundary}\r\n";
                    $headers .= $val->create();
                }
            }

            $headers .= "--{$this->boundary}--\r\n";
        } else {

            $headers .= "Content-Type: {$this->content_type} charset=UTF-8; \r\n" .
                $addheaders . "\r\n\r\n{$this->content}";

        }
        return $headers;
    }

    public function add_header($header)
    {
        $this->add_header[] = $header;
    }

    public function get_add_headers()
    {
        return $this->add_header;
    }

    public function get_content_type()
    {
        return $this->content_type;
    }

    public function set_content_type($newval)
    {
        $this->content_type = $newval;
    }

    public function get_content_enc()
    {
        return $this->content_enc;
    }

    public final function set_content_enc($newval)
    {
        $this->content_enc = $newval;
    }

    public function get_content()
    {
        return $this->content;
    }

    public function set_content($string)
    {
        $this->content = base64_encode($string);
    }

    public final function add_subcontainer($container)
    {
        $this->subcontainers[] = $container;
    }

    public final function get_subcontainers()
    {
        return $this->subcontainers;
    }

    public function mb_chunk_split($str, $len, $glue)
    {
        if (empty($str)) {
            return false;
        }
        $array = $this->mbStringToArray($str);
        $n = 0;
        $new = '';
        foreach ($array as $char) {
            if ($n < $len) {
                $new .= $char;
            } elseif ($n == $len) {
                $new .= $glue . $char;
                $n = 0;
            }
            $n++;
        }
        return $new;
    }

    /*
     * Para hacer el chunk_split binarysafe, pero no funciona para
     * cadenas largas (500K). No váliod para los attachments
     */

    protected function mbStringToArray($str)
    {
        if (empty($str)) {
            return false;
        }
        $len = mb_strlen($str);
        $array = array();
        for ($i = 0; $i < $len; $i++) {
            $array[] = mb_substr($str, $i, 1);
        }
        return $array;
    }

}
