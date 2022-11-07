<?php

namespace expedientes\model;

class FormatoLista
{
    private string $pagina_nueva = '';
    private string $pagina_mod = '';
    private string $pagina_ver = '';
    private bool $columna_mod_visible = FALSE;
    private bool $columna_ver_visible = FALSE;
    private bool $columna_f_ini_visible = FALSE;
    private string $txt_columna_ver = '';
    private string $txt_columna_mod = '';
    private int $presentacion = 0;

    /**
     * @return string
     */
    public function getPaginaNueva(): string
    {
        return $this->pagina_nueva;
    }

    /**
     * @param string $pagina_nueva
     */
    public function setPaginaNueva(string $pagina_nueva): void
    {
        $this->pagina_nueva = $pagina_nueva;
    }

    /**
     * @return string
     */
    public function getPaginaMod(): string
    {
        return $this->pagina_mod;
    }

    /**
     * @param string $pagina_mod
     */
    public function setPaginaMod(string $pagina_mod): void
    {
        $this->pagina_mod = $pagina_mod;
    }

    /**
     * @return string
     */
    public function getPaginaVer(): string
    {
        return $this->pagina_ver;
    }

    /**
     * @param string $pagina_ver
     */
    public function setPaginaVer(string $pagina_ver): void
    {
        $this->pagina_ver = $pagina_ver;
    }

    /**
     * @return bool
     */
    public function isColumnaModVisible(): bool
    {
        return $this->columna_mod_visible;
    }

    /**
     * @param bool $columna_mod_visible
     */
    public function setColumnaModVisible(bool $columna_mod_visible): void
    {
        $this->columna_mod_visible = $columna_mod_visible;
    }

    /**
     * @return bool
     */
    public function isColumnaVerVisible(): bool
    {
        return $this->columna_ver_visible;
    }

    /**
     * @param bool $columna_ver_visible
     */
    public function setColumnaVerVisible(bool $columna_ver_visible): void
    {
        $this->columna_ver_visible = $columna_ver_visible;
    }

    /**
     * @return bool
     */
    public function isColumnaFIniVisible(): bool
    {
        return $this->columna_f_ini_visible;
    }

    /**
     * @param bool $columna_f_ini_visible
     */
    public function setColumnaFIniVisible(bool $columna_f_ini_visible): void
    {
        $this->columna_f_ini_visible = $columna_f_ini_visible;
    }

    /**
     * @return string
     */
    public function getTxtColumnaVer(): string
    {
        return $this->txt_columna_ver;
    }

    /**
     * @param string $txt_columna_ver
     */
    public function setTxtColumnaVer(string $txt_columna_ver): void
    {
        $this->txt_columna_ver = $txt_columna_ver;
    }

    /**
     * @return string
     */
    public function getTxtColumnaMod(): string
    {
        return $this->txt_columna_mod;
    }

    /**
     * @param string $txt_columna_mod
     */
    public function setTxtColumnaMod(string $txt_columna_mod): void
    {
        $this->txt_columna_mod = $txt_columna_mod;
    }

    /**
     * @return int
     */
    public function getPresentacion(): int
    {
        return $this->presentacion;
    }

    /**
     * @param int $presentacion
     */
    public function setPresentacion(int $presentacion): void
    {
        $this->presentacion = $presentacion;
    }


}