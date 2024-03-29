<?php

namespace envios\model;

class FicherosPSWin
{

    private string $dir_base_win;
    private string $dir_rdp_win;
    private string $file_ps1;
    private string $fullFileLog;
    private string $DIR_CORREO;

    public function __construct($DIR_CORREO)
    {

        $this->DIR_CORREO = $DIR_CORREO;
    }

    /**
     * poner a parte de construct, para poder cambiar las rutas con;
     *      setDirBaseWin
     *      setDirRdpWin
     *
     * @return void
     */
    public function inicializar(): void
    {
        $this->inicializar_rutas();
        $this->inicializar_ficheros();
    }

    private function inicializar_rutas(): void
    {
        $fecha = date('Ymd');

        $fileLog = $fecha . ".log";
        $fileBat = "enviar_rdp.ps1";
        $this->file_ps1 = $this->DIR_CORREO . '/' . $fileBat;

        // valores por defecto
        if (empty($this->dir_base_win)) {
            $this->dir_base_win = "m:\\z-trasvase\\tramity";
        }
        if (empty($this->dir_rdp_win)) {
            $any = date('Y');
            $this->dir_rdp_win = "m:\\De_dlp_".$any."\\";
        }

        $this->fullFileLog = $this->dir_base_win . '\\' . $fileLog;
    }

    private function inicializar_ficheros(): void
    {
        if (!file_exists($this->file_ps1)) {
            $txt = '$' . 'cls =New-Object -com Fsrm.FsrmClassificationManager';
            file_put_contents($this->file_ps1, $txt);
        } else {
            $this->remove_pause();
        }
    }

    private function remove_pause()
    {
        $content = file_get_contents($this->file_ps1);
        $newcontent = str_replace("\r\n"."pause"."\r\n", "", $content);
        file_put_contents($this->file_ps1, $newcontent);
    }

    public function add_pause(): void
    {
        $cmd_ps1 = "\r\n";
        $cmd_ps1 .= 'pause';
        $cmd_ps1 .= "\r\n";
        $cmd_ps1 = mb_convert_encoding($cmd_ps1, 'ISO-8859-1', 'UTF-8');
        file_put_contents($this->file_ps1, $cmd_ps1, FILE_APPEND);
    }

    public function permisos($filename, $permisos)
    {
        $fullFilename = $this->dir_base_win . '\\' . $filename;

        $cmd_ps1 = "\r\n";
        $cmd_ps1 .= "echo Cambiando permisos de \"$fullFilename\" >> $this->fullFileLog 2>&1";
        $cmd_ps1 = mb_convert_encoding($cmd_ps1, 'ISO-8859-1', 'UTF-8');
        file_put_contents($this->file_ps1, $cmd_ps1, FILE_APPEND);

        $cmd_ps1 = "\r\n";
        $cmd_ps1 .= '$' . "p= " . '$' . "cls.SetFileProperty(\"$fullFilename\", \"Destino_88d2f0439f068627\", \"$permisos\") 2>&1";
        $cmd_ps1 = mb_convert_encoding($cmd_ps1, 'ISO-8859-1', 'UTF-8');
        file_put_contents($this->file_ps1, $cmd_ps1, FILE_APPEND);
    }

    public function mover($filename)
    {

        $fullFilename = $this->dir_base_win . '\\' . $filename;

        $cmd_ps1 = "\r\n";
        $cmd_ps1 .= "echo Moviendo \"$fullFilename\" >> $this->fullFileLog 2>&1";
        $cmd_ps1 = mb_convert_encoding($cmd_ps1, 'ISO-8859-1', 'UTF-8');
        file_put_contents($this->file_ps1, $cmd_ps1, FILE_APPEND);

        $cmd_ps1 = "\r\n";
        $cmd_ps1 .= "MOVE \"$fullFilename\" \"$this->dir_rdp_win\" >> $this->fullFileLog 2>&1";
        $cmd_ps1 = mb_convert_encoding($cmd_ps1, 'ISO-8859-1', 'UTF-8');
        file_put_contents($this->file_ps1, $cmd_ps1, FILE_APPEND);
    }

    public function setDirBaseWin(string $dir_base_win): void
    {
        $this->dir_base_win = $dir_base_win;
    }

    public function setDirRdpWin(string $dir_rdp_win): void
    {
        $this->dir_rdp_win = $dir_rdp_win;
    }
}