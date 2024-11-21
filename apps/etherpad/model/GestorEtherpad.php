<?php

namespace etherpad\model;


use escritos\model\TextoDelEscrito;

class GestorEtherpad
{

    /**
     * El force conviene que sea un string
     */
    public function moveDocToAdjunto($id_doc, $id_adjunto, $force = 'false')
    {
        // No uso el comando move, para borrar la historia.

        $this->copyDocToAdjunto($id_doc, $id_adjunto, $force);
        // borrar el Doc
        $oEtherpadSource = new Etherpad();
        $oEtherpadSource->setId(TextoDelEscrito::ID_DOCUMENTO, $id_doc);
        $oEtherpadSource->crearTexto();
        $sourceID = $oEtherpadSource->getPadId();

        $rta = $oEtherpadSource->deletePad($sourceID);
        if ($rta->getCode() == 0) {
            /* Example returns:
             * {code: 0, message:"ok", data: null}
             * {code: 1, message:"padID does not exist", data: null}
             */
        } else {
            return $oEtherpadSource->mostrar_error($rta);
        }
    }

    /**
     * El force conviene que sea un string
     */
    public function copyDocToAdjunto($id_doc, $id_adjunto, $force = 'false')
    {
        $oEtherpad = new Etherpad();
        $oEtherpad->setId(TextoDelEscrito::ID_DOCUMENTO, $id_doc);
        $oEtherpad->crearTexto();
        $sourceID = $oEtherpad->getPadId();

        $oNewEtherpad = new Etherpad();
        $oNewEtherpad->setId(TextoDelEscrito::ID_ADJUNTO, $id_adjunto);
        $oNewEtherpad->crearTexto();
        $destinationID = $oNewEtherpad->getPadId();

        $rta = $oEtherpad->copyPadWithoutHistory($sourceID, $destinationID, $force);
        if ($rta->getCode() == 0) {
            /* Example returns:
             * {code: 0, message:"ok", data: null}
             * {code: 1, message:"padID does not exist", data: null}
             */
        } else {
            return $oEtherpad->mostrar_error($rta);
        }
    }

    /**
     * El force conviene que sea un string
     */
    public function moveDocToEscrito($id_doc, $id_escrito, $force = 'false')
    {
        // No uso el comando move, para borrar la historia.

        $this->copyDocToEscrito($id_doc, $id_escrito, $force);
        // borrar el Doc
        $oEtherpadSource = new Etherpad();
        $oEtherpadSource->setId(TextoDelEscrito::ID_DOCUMENTO, $id_doc);
        $oEtherpadSource->crearTexto();
        $sourceID = $oEtherpadSource->getPadId();

        $rta = $oEtherpadSource->deletePad($sourceID);
        if ($rta->getCode() == 0) {
            /* Example returns:
             * {code: 0, message:"ok", data: null}
             * {code: 1, message:"padID does not exist", data: null}
             */
        } else {
            return $oEtherpadSource->mostrar_error($rta);
        }
    }

    /**
     * El force conviene que sea un string
     */
    public function copyDocToEscrito($id_doc, $id_escrito, $force = 'false')
    {
        $oEtherpad = new Etherpad();
        $oEtherpad->setId(TextoDelEscrito::ID_DOCUMENTO, $id_doc);
        $oEtherpad->crearTexto();
        $sourceID = $oEtherpad->getPadId();

        $oNewEtherpad = new Etherpad();
        $oNewEtherpad->setId(TextoDelEscrito::ID_ESCRITO, $id_escrito);
        $oNewEtherpad->crearTexto();
        $destinationID = $oNewEtherpad->getPadId();

        // Por alguna razón el force no funciona. Hay que eliminarlo primero:
        // Quizá porque no se le pasaba como string. Habría que probar si ahora funciona.
        $rta = $oNewEtherpad->deletePad($destinationID);
        if ($rta->getCode() == 0) {
            /* Example returns:
             * {code: 0, message:"ok", data: null}
             * {code: 1, message:"padID does not exist", data: null}
             */
            $rta = $oEtherpad->copyPadWithoutHistory($sourceID, $destinationID, $force);
            if ($rta->getCode() == 0) {
                /* Example returns:
                 * {code: 0, message:"ok", data: null}
                 * {code: 1, message:"padID does not exist", data: null}
                 */
            } else {
                return $oEtherpad->mostrar_error($rta);
            }
        } else {
            return $oNewEtherpad->mostrar_error($rta);
        }
    }

}
   