<?php

namespace usuarios\domain;

class Categoria
{
    /* CONST -------------------------------------------------------------- */

    // categoría
    public const CAT_E12 = 1;
    public const CAT_NORMAL = 2;
    public const CAT_PERMANENTE = 3;


    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    public function getArrayCategoria(): array
    {
        return [
            self::CAT_NORMAL => _("normal"),
            self::CAT_E12 => _("sin numerar"),
            self::CAT_PERMANENTE => _("permanente"),
        ];
    }

}