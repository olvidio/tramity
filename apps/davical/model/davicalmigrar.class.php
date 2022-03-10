<?php
namespace davical\model;

/**
 * Para cambiar todos los eventos de una determinada entrada a otra.
 * 
 * @author dani
 *
 */
class DavicalMigrar {
    
    /**
     * oDbl de DavicalMigrar
     *
     * @var string
     */
    private $oDbl;
    
    /**
     * oficina de DavicalMigrar
     *
     * @var integer
     */
    private $id_oficina;
    
    /**
     * dav_name de DavicalMigrar
     *
     * @var string
     */
    private $dav_name;
    
    /**
     * id_reg_org de DavicalMigrar
     *
     * @var string
     */
    private $id_reg_org;
    
    /**
     * id_reg_dst de DavicalMigrar
     *
     * @var string
     */
    private $id_reg_dst;
    
    /**
     * location de DavicalMigrar
     *
     * @var string
     */
    private $location_org;
    
    /**
     * location_dst de DavicalMigrar
     *
     * @var string
     */
    private $location_dst;
    
    /*
    dav_name		/oficina_scdl/registro/REN532689-20210316T101739.ics
    
    caldav_data  	UID:REN532689-20210316T101739@registro_oficina_scdl
                    X-DLB-ID-REG:532689
    uid				REN532689-20210316T101739@registro_oficina_scdl
    */
    
    /* CONSTRUCTOR -------------------------------------------------------------- */

    /**
     * Constructor de la classe.
     *
     * @return $gestor
     *
     */
    function __construct() {
    	$oDbl = $GLOBALS['oDBDavical'];

        $this->setoDbl($oDbl);
    }
    
    
    /* METODES PUBLICS ----------------------------------------------------------*/
    
    /*
     * Hay que modificar las tablas de:
     * caldav_data
     * calendar_item
     * sync_changes
     * 
     */
    public function migrar() {
        
        if ( $this->migrarCaldav_data() === FALSE ) {
            return FALSE;
        }
        // por claves foráneas, debe ir despues del caldav_data.
        if ($this->migrarCalendar_item() === FALSE ) {
            return FALSE;
        }
        if ($this->migrarSyncChanges() === FALSE ) {
            return FALSE;
        }
        
        return TRUE;
    }
    
    
    /**
     * Hay que cambiar el campo dav_name y uid
     * 
     * ejemplo:
     *      dav_name	/dlb_oficina_scdl/oficina/OFEN532557-20210526T112857.ics	/oficina_scdl/registro/REN532689-20210316T101739.ics
     *      uid			OFEN532557-20210526T112857@registro_oficina_scdl
     * 
     * @return boolean
     */
    private function migrarCalendar_item() {
        $oDbl = $this->getoDbl();
        
        $dav_name_dst = $this->getDavNameDst();
        
        $uid_org = $this->getUidOrg();
        $uid_dst = $this->getUidDst();
        
        $location_org = $this->getLocation_org();
        $location_dst = $this->getLocation_dst();
        
        // El dav_name se ha cambiado al cambiar la tabla caldav_data
        //            SET dav_name = replace(dav_name,'$dav_name_org','$dav_name_dst'),
        $sQuery = "UPDATE calendar_item 
                    SET uid = replace(uid,'$uid_org','$uid_dst'),
                        location = replace(location,'$location_org','$location_dst')
                    WHERE dav_name ~ '^$dav_name_dst'";
        
        if (($oDbl->query($sQuery)) === FALSE) {
            $sClauError = 'MigrarCalendarItem.query';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }

    /**
     * Hay que cambiar el campo dav_name
     *
     * ejemplo:
     *      dav_name	/dlb_oficina_scdl/oficina/OFEN532557-20210526T112857.ics
     *
     * @return boolean
     */
    private function migrarSyncChanges() {
        $oDbl = $this->getoDbl();
        
        $dav_name_org = $this->getDavNameOrg();
        $dav_name_dst = $this->getDavNameDst();
        
        $sQuery = "UPDATE sync_changes 
                    SET dav_name = replace(dav_name,'$dav_name_org','$dav_name_dst')
                    WHERE dav_name ~ '^$dav_name_org'";
        
        if (($oDbl->query($sQuery)) === FALSE) {
            $sClauError = 'MigrarSyncChanges.query';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            return FALSE;
        }
        return TRUE;
    }
    
    /**
     * Hay que cambiar el campo dav_name y uid
     *
     * ejemplo:
     *      dav_name		/oficina_scdl/registro/REN532689-20210316T101739.ics
     *      caldav_data  	UID:REN532689-20210316T101739@registro_oficina_scdl
	 *	 	             	X-DLB-ID-REG:532689
	 *                      LOCATION:agdBalandrau 1/20\, i més\r
     *
     *  /dlb_oficina_vsr/registro/REN533155-20210323T092822.ics
     * 	(...)
     *	UID:REN533155-20210323T092822@registro_dlb_oficina_vsr
     *	LOCATION:cr 1/11\r
     *	X-DLB-ID-REG:REN533155\r
     *
     *
     * @return boolean
     */
    private function migrarCaldav_data() {
        $oDbl = $this->getoDbl();
        
        $dav_name_org = $this->getDavNameOrg();
        $dav_name_dst = $this->getDavNameDst();
        
        $dlb_id_org = 'X-DLB-ID-REG:'.$this->getUidOrg();
        $dlb_id_dst = 'X-DLB-ID-REG:'.$this->getUidDst();
        
        $uid_org = 'UID:'.$this->getUidOrg();
        $uid_dst = 'UID:'.$this->getUidDst();
        
        $location = $this->getLocation_dst();
        
        //UPDATE caldav_data SET caldav_data = REGEXP_REPLACE(caldav_data,'UID:(.?)@(.)_oficina_(.*?)\r','UID:\1@\2_dlb_oficina_\3')
        // hay que hacerlas en orden, para no perder el Where: dav_name.
        $sQuery_1 = "UPDATE caldav_data
                    SET caldav_data = REGEXP_REPLACE(caldav_data,'LOCATION:(.*?)(,.*)?\r','LOCATION:$location\\2')
                    WHERE dav_name ~ '^$dav_name_org'";
        
        $sQuery_2 = "UPDATE caldav_data
                    SET caldav_data = replace(caldav_data,'$dlb_id_org','$dlb_id_dst')
                    WHERE dav_name ~ '^$dav_name_org'";
        $sQuery_3 = "UPDATE caldav_data
                    SET dav_name = replace(dav_name,'$dav_name_org','$dav_name_dst'),
                        caldav_data = replace(caldav_data,'$uid_org','$uid_dst')
                    WHERE dav_name ~ '^$dav_name_org'";
        
        $err = FALSE;
        $oDbl->beginTransaction();
        
        if (($oDbl->exec($sQuery_1)) === FALSE) {
            $sClauError = 'MigrarCaldav.query1';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            $err = TRUE;
        }
        if (($oDbl->exec($sQuery_2)) === FALSE) {
            $sClauError = 'MigrarCaldav.query2';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            $err = TRUE;
        }
        if (($oDbl->exec($sQuery_3)) === FALSE) {
            $sClauError = 'MigrarCaldav.query3';
            $_SESSION['oGestorErrores']->addErrorAppLastError($oDbl, $sClauError, __LINE__, __FILE__);
            $err = TRUE;
        }
         
        if ($err) {
            $oDbl->rollBack();
            return FALSE;
        } else {
            $oDbl->commit();
            return TRUE;
        }
    }
    
    private function getUidOrg() {
        return $this->getId_reg_org()."-";
           
    }
    private function getUidDst() {
        return $this->getId_reg_dst()."-";
    }
    private function getDavNameOrg() {
    	$oDavical = new Davical($_SESSION['oConfig']->getAmbito());
    	$parent_container = $oDavical->getNombreRecurso($this->id_oficina);
    	$dav_name = '/'.$parent_container.'/registro/'.$this->getId_reg_org()."-";
        
        return $dav_name;
    }
    private function getDavNameDst() {
    	$oDavical = new Davical($_SESSION['oConfig']->getAmbito());
    	$parent_container = $oDavical->getNombreRecurso($this->id_oficina);
    	$dav_name = '/'.$parent_container.'/registro/'.$this->getId_reg_dst()."-";
        
        return $dav_name;
    }
    /**
     * @return string
     */
    public function getoDbl()
    {
        return $this->oDbl;
    }

    /**
     * @param string $oDbl
     */
    public function setoDbl($oDbl)
    {
        $this->oDbl = $oDbl;
    }

    /**
     * @return string
     */
    public function getId_oficina()
    {
        return $this->id_oficina;
    }

    /**
     * @param integer $id_oficina
     */
    public function setId_oficina($id_oficina)
    {
        $this->id_oficina = $id_oficina;
    }

    /**
     * @return string
     */
    public function getDav_name()
    {
        return $this->dav_name;
    }

    /**
     * @param string $dav_name
     */
    public function setDav_name($dav_name)
    {
        $this->dav_name = $dav_name;
    }

    /**
     * @return string
     */
    public function getId_reg_org()
    {
        return $this->id_reg_org;
    }

    /**
     * @param string $id_reg_org
     */
    public function setId_reg_org($id_reg_org)
    {
        $this->id_reg_org = $id_reg_org;
    }

    /**
     * @return string
     */
    public function getId_reg_dst()
    {
        return $this->id_reg_dst;
    }

    /**
     * @param string $id_reg_dst
     */
    public function setId_reg_dst($id_reg_dst)
    {
        $this->id_reg_dst = $id_reg_dst;
    }
    /**
     * @return string
     */
    public function getLocation_dst()
    {
        return $this->location_dst;
    }

    /**
     * @param string $location
     */
    public function setLocation_dst($location_dst)
    {
        $this->location_dst = $location_dst;
    }
    /**
     * @return string
     */
    public function getLocation_org()
    {
        return $this->location_org;
    }

    /**
     * @param string $location_org
     */
    public function setLocation_org($location_org)
    {
        $this->location_org = $location_org;
    }

}