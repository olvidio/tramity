<?php

namespace usuarios\model;

class Categoria
{


    /* CONST -------------------------------------------------------------- */

    // categoria
    const CAT_E12 = 1;
    const CAT_NORMAL = 2;
    const CAT_PERMANENTE = 3;


    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    public function getArrayCategoria()
    {
        return [
            self::CAT_NORMAL => _("normal"),
            self::CAT_E12 => _("sin numerar"),
            self::CAT_PERMANENTE => _("permanente"),
        ];
    }
}