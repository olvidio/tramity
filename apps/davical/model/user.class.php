<?php
namespace davical\model;

use davical\model\entity\UserDB;
/**
 * Fitxer amb la Classe que accedeix a la taula aux_usuarios
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 4/6/2020
 */
/**
 * Classe que implementa l'entitat aux_usuarios
 *
 * @package tramity
 * @subpackage model
 * @author Daniel Serrabou
 * @version 1.0
 * @created 4/6/2020
 */
class User Extends UserDB {
    
    /* CONSTRUCTOR -------------------------------------------------------------- */

    
    /* METODES PUBLICS ----------------------------------------------------------*/
    
    public function cambiarNombre($nom_new, $nom_old) {
        $oDbl = $this->getoDbl();
        $nom_tabla = $this->getNomTabla();
        
        $sQry = "UPDATE $nom_tabla SET username='$nom_new' WHERE username='$nom_old'; ";
        
        if (($oDbl->query($sQry)) === FALSE) {
            $sClauError = 'DavicalUser.cambioNombre';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        
        $sQry = "SELECT user_no FROM $nom_tabla WHERE username='$nom_new' ";
        if (($oDbl->query($sQry)) === FALSE) {
            $sClauError = 'DavicalUser.cambioNombre';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        foreach ($oDbl->query($sQry) as $row) {
            $user_no = $row['user_no'];
        }
        
        return $user_no;
        
    }
    
}