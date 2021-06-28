<?php
namespace etherpad\model;


class GestorEtherpad {
    
    public function copyDocToAdjunto($id_doc, $id_adjunto, $force=false) {
        $oEtherpad = new Etherpad();
        $oEtherpad->setId(Etherpad::ID_DOCUMENTO,$id_doc);
        $sourceID = $oEtherpad->getPadId();
        
        $oNewEtherpad = new Etherpad();
        $oNewEtherpad->setId(Etherpad::ID_ADJUNTO, $id_adjunto);
        $destinationID = $oNewEtherpad->getPadID();
        
        $rta = $oEtherpad->copyPadWithoutHistory($sourceID, $destinationID, 'true');
        if ($rta->getCode() == 0) {
            /* Example returns:
             * {code: 0, message:"ok", data: null}
             * {code: 1, message:"padID does not exist", data: null}
             */
        } else {
            return $this->mostrar_error($rta);
        }
    }
    
    public function moveDocToAdjunto($id_doc, $id_adjunto, $force=false) {
        // No uso el comando move, para borrar la historia.
        
        $this->copyDocToAdjunto($id_doc, $id_adjunto, $force);
        // borrar el Doc
        $oEtherpadSource = new Etherpad();
        $oEtherpadSource->setId(Etherpad::ID_DOCUMENTO,$id_doc);
        $sourceID = $oEtherpadSource->getId_pad();
        
        $rta = $oEtherpadSource->deletePad($sourceID);
        if ($rta->getCode() == 0) {
            /* Example returns:
             * {code: 0, message:"ok", data: null}
             * {code: 1, message:"padID does not exist", data: null}
             */
        } else {
            return $this->mostrar_error($rta);
        }
    }
    
    public function copyDocToEscrito($id_doc, $id_escritp, $force=false) {
        $oEtherpad = new Etherpad();
        $oEtherpad->setId(Etherpad::ID_DOCUMENTO,$id_doc);
        $sourceID = $oEtherpad->getPadId();
        
        $oNewEtherpad = new Etherpad();
        $oNewEtherpad->setId(Etherpad::ID_ESCRITO, $id_escritp);
        $destinationID = $oNewEtherpad->getPadID();
        
        $rta = $oEtherpad->copyPadWithoutHistory($sourceID, $destinationID, 'true');
        if ($rta->getCode() == 0) {
            /* Example returns:
             * {code: 0, message:"ok", data: null}
             * {code: 1, message:"padID does not exist", data: null}
             */
        } else {
            return $this->mostrar_error($rta);
        }

    }
    
    public function moveDocToEscrito($id_doc, $id_escrito, $force=false) {
        // No uso el comando move, para borrar la historia.
        
        $this->copyDocToEscrito($id_doc, $id_escrito, $force);
        // borrar el Doc
        $oEtherpadSource = new Etherpad();
        $oEtherpadSource->setId(Etherpad::ID_DOCUMENTO,$id_doc);
        $sourceID = $oEtherpadSource->getId_pad();
        
        $rta = $oEtherpadSource->deletePad($sourceID);
        if ($rta->getCode() == 0) {
            /* Example returns:
             * {code: 0, message:"ok", data: null}
             * {code: 1, message:"padID does not exist", data: null}
             */
        } else {
            return $this->mostrar_error($rta);
        }
    }
    
}
   