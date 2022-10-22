<?php

namespace oasis_as4\model;

use DOMAttr;
use DOMDocument;

class Pmode extends As4CollaborationInfo
{

    /* CONST -------------------------------------------------------------- */

    /* PROPIEDADES -------------------------------------------------------------- */
    /**
     *
     * @var object
     */
    private $dom;

    private $holo_server_dst;
    private $delivery_format;

    public function __construct()
    {
        /* create a dom document with encoding utf8 */
        $this->dom = new DOMDocument('1.0', 'UTF-8');
    }


    /* MÉTODOS PÚBLICOS ----------------------------------------------------------*/

    /**
     *
     */
    public function saveResp()
    {
        $error_txt = '';
        $this->delivery_format = 'mmd';
        $full_filename = $this->getNameResp();

        $this->dom->preserveWhiteSpace = false;
        $this->dom->formatOutput = true;

        $this->dom->appendChild($this->getPmodeResp());

        if ($this->dom->save($full_filename) === FALSE) {
            $error_txt .= _("Error al escribir el pmode resp");
        }
        return $error_txt;
    }

    public function getNameResp()
    {
        $filename = $this->getPm_id() . '-resp';
        $dir = $_SESSION['oConfig']->getDock();
        return $dir . '/repository/pmodes_resp/' . $filename . '.xml';
    }

    private function getPmodeResp()
    {
        $pm_id = $this->getPm_id();

        // crear el nodo:
        $pmode = $this->dom->createElement("PMode");
        $attr_1 = new DOMAttr('xmlns', "http://holodeck-b2b.org/schemas/2014/10/pmode");
        $pmode->setAttributeNode($attr_1);
        $attr_1 = new DOMAttr('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
        $pmode->setAttributeNode($attr_1);
        $attr_1 = new DOMAttr('xsi:schemaLocation', "http://holodeck-b2b.org/schemas/2014/10/pmode ../../repository/xsd/pmode.xsd");
        $pmode->setAttributeNode($attr_1);

        $id = $this->dom->createElement("id", $pm_id);
        $pmode->appendChild($id);

        $mep_val = 'http://docs.oasis-open.org/ebxml-msg/ebms/v3.0/ns/core/200704/oneWay';
        $mep = $this->dom->createElement("mep", $mep_val);
        $pmode->appendChild($mep);

        $mepb_val = 'http://docs.oasis-open.org/ebxml-msg/ebms/v3.0/ns/core/200704/push';
        $mepb = $this->dom->createElement("mepBinding", $mepb_val);
        $pmode->appendChild($mepb);

        $pmode->appendChild($this->getInitiator());
        $pmode->appendChild($this->getResponder());
        $pmode->appendChild($this->getAgreement());
        $pmode->appendChild($this->getLegResp());

        return $pmode;
    }

    private function getInitiator()
    {
        $plataforma_origen = $this->getPlataforma_Origen();
        // crear el nodo:
        $responder = $this->dom->createElement("Initiator");

        $type = 'urn:oasis:names:tc:ebcore:partyid-type:unregistered:plataformas';

        $party = $this->dom->createElement('PartyId', $plataforma_origen);
        $attr_1 = new DOMAttr("type", $type);
        $party->setAttributeNode($attr_1);
        $responder->appendChild($party);

        $role = $this->dom->createElement("Role", "Sender");
        $responder->appendChild($role);

        return $responder;
    }

    private function getResponder()
    {
        $plataforma_destino = $this->getPlataforma_Destino();
        // crear el nodo:
        $responder = $this->dom->createElement("Responder");

        $type = 'urn:oasis:names:tc:ebcore:partyid-type:unregistered:plataformas';

        $party = $this->dom->createElement('PartyId', $plataforma_destino);
        $attr_1 = new DOMAttr("type", $type);
        $party->setAttributeNode($attr_1);
        $responder->appendChild($party);

        $role = $this->dom->createElement("Role", "Receiver");
        $responder->appendChild($role);

        return $responder;
    }

    private function getAgreement()
    {
        // crear el nodo:
        $agreement = $this->dom->createElement("Agreement");
        $value = 'http://agreements.moneders.net/cpa/PEC21';
        $name = $this->dom->createElement("name", $value);

        $agreement->appendChild($name);

        return $agreement;
    }

    private function getLegResp()
    {
        // crear el nodo:
        $leg = $this->dom->createElement("Leg");

        $leg->appendChild($this->getReceiptResp());
        $leg->appendChild($this->getDefaultDelivery());
        $leg->appendChild($this->getUserMessageFlowResp());

        return $leg;
    }

    private function getReceiptResp()
    {
        // crear el nodo:
        $receipt = $this->dom->createElement("Receipt");
        $notify = $this->dom->createElement("ReplyPattern", "RESPONSE");

        $receipt->appendChild($notify);

        return $receipt;
    }

    private function getDefaultDelivery()
    {
        // crear el nodo:
        $default_delivery = $this->dom->createElement("DefaultDelivery");

        $default_delivery->appendChild($this->getDeliveryMethod());
        $default_delivery->appendChild($this->getParameter('format', $this->delivery_format));
        $default_delivery->appendChild($this->getParameter('deliveryDirectory', 'data/msg_in'));

        return $default_delivery;
    }

    private function getDeliveryMethod()
    {
        $value = 'org.holodeckb2b.backend.file.NotifyAndDeliverOperation';
        $delivery_method = $this->dom->createElement("DeliveryMethod", $value);

        return $delivery_method;
    }

    private function getParameter($nombre, $valor)
    {
        // crear el nodo:
        $parameter = $this->dom->createElement("Parameter");

        $name = $this->dom->createElement("name");
        $name->appendChild($this->dom->createTextNode($nombre));
        $value = $this->dom->createElement("value");
        $value->appendChild($this->dom->createTextNode($valor));
        $parameter->appendChild($name);
        $parameter->appendChild($value);

        return $parameter;
    }

    private function getUserMessageFlowResp()
    {
        // crear el nodo:
        $user_message_flow = $this->dom->createElement("UserMessageFlow");

        $user_message_flow->appendChild($this->getBusinessInfo());

        return $user_message_flow;
    }

    private function getBusinessInfo()
    {
        $accion_txt = $this->getAccion();
        // crear el nodo:
        $bussines_info = $this->dom->createElement("BusinessInfo");

        $action = $this->dom->createElement("Action");
        $action->appendChild($this->dom->createTextNode($accion_txt));
        $bussines_info->appendChild($action);

        $service = $this->dom->createElement("Service");
        $name = $this->dom->createElement("name", 'Correo');
        $type = $this->dom->createElement("type", 'org:tramity:services');
        $service->appendChild($name);
        $service->appendChild($type);

        $bussines_info->appendChild($service);

        return $bussines_info;
    }

    /**
     *
     */
    public function saveInit()
    {
        $error_txt = '';
        $this->delivery_format = 'ebms';
        $full_filename = $this->getNameInit();

        $this->dom->preserveWhiteSpace = false;
        $this->dom->formatOutput = true;

        $this->dom->appendChild($this->getPmodeInit());

        if ($this->dom->save($full_filename) === FALSE) {
            $error_txt .= _("Error al escribir el pmode init");
        }
        return $error_txt;
    }

    public function getNameInit()
    {
        $filename = $this->getPm_id() . '-init';
        $dir = $_SESSION['oConfig']->getDock();
        return $dir . '/repository/pmodes/' . $filename . '.xml';
    }

    private function getPmodeInit()
    {
        $pm_id = $this->getPm_id();

        // crear el nodo:
        $pmode = $this->dom->createElement("PMode");
        $attr_1 = new DOMAttr('xmlns', "http://holodeck-b2b.org/schemas/2014/10/pmode");
        $pmode->setAttributeNode($attr_1);
        $attr_1 = new DOMAttr('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
        $pmode->setAttributeNode($attr_1);
        $attr_1 = new DOMAttr('xsi:schemaLocation', "http://holodeck-b2b.org/schemas/2014/10/pmode ../../repository/xsd/pmode.xsd");
        $pmode->setAttributeNode($attr_1);

        $id = $this->dom->createElement("id", $pm_id);
        $pmode->appendChild($id);

        $mep_val = 'http://docs.oasis-open.org/ebxml-msg/ebms/v3.0/ns/core/200704/oneWay';
        $mep = $this->dom->createElement("mep", $mep_val);
        $pmode->appendChild($mep);

        $mepb_val = 'http://docs.oasis-open.org/ebxml-msg/ebms/v3.0/ns/core/200704/push';
        $mepb = $this->dom->createElement("mepBinding", $mepb_val);
        $pmode->appendChild($mepb);

        $pmode->appendChild($this->getInitiator());
        $pmode->appendChild($this->getResponder());
        $pmode->appendChild($this->getAgreement());
        $pmode->appendChild($this->getLeg());

        return $pmode;
    }

    private function getLeg()
    {
        // crear el nodo:
        $leg = $this->dom->createElement("Leg");

        $leg->appendChild($this->getProtocol());
        $leg->appendChild($this->getReceipt());
        $leg->appendChild($this->getDefaultDelivery());
        $leg->appendChild($this->getUserMessageFlow());

        return $leg;
    }

    private function getProtocol()
    {
        // crear el nodo:
        $protocol = $this->dom->createElement("Protocol");
        $value = $this->holo_server_dst;
        $address = $this->dom->createElement("Address", $value);

        $protocol->appendChild($address);

        return $protocol;
    }

    private function getReceipt()
    {
        // crear el nodo:
        $receipt = $this->dom->createElement("Receipt");
        $notify = $this->dom->createElement("NotifyReceiptToBusinessApplication", "true");

        $receipt->appendChild($notify);

        return $receipt;
    }

    private function getUserMessageFlow()
    {
        // crear el nodo:
        $user_message_flow = $this->dom->createElement("UserMessageFlow");

        $user_message_flow->appendChild($this->getBusinessInfo());
        $user_message_flow->appendChild($this->getErrorHandling());
        $user_message_flow->appendChild($this->getPayloadProfile());

        return $user_message_flow;
    }

    private function getErrorHandling()
    {
        // crear el nodo:
        $error_handling = $this->dom->createElement("ErrorHandling");

        $notify = $this->dom->createElement("NotifyErrorToBusinessApplication");
        $notify->appendChild($this->dom->createTextNode('true'));

        $error_handling->appendChild($notify);

        return $error_handling;
    }

    private function getPayloadProfile()
    {
        // crear el nodo:
        $payload_profile = $this->dom->createElement("PayloadProfile");

        $use_compression = $this->dom->createElement("UseAS4Compression");
        $use_compression->appendChild($this->dom->createTextNode('true'));

        $payload_profile->appendChild($use_compression);

        return $payload_profile;
    }

    /**
     * @return mixed
     */
    public function getHolo_server_dst()
    {
        return $this->holo_server_dst;
    }

    /**
     * @param mixed $holo_server_dst
     */
    public function setHolo_server_dst($holo_server_dst)
    {
        $this->holo_server_dst = $holo_server_dst;
    }

}