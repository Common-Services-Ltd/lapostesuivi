<?php
/**
 * @author    debuss-a <alexandre@common-services.com>
 * @copyright Copyright (c) 2018 Common-Services
 * @license   CC BY-SA 4.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Class Response
 */
class Response
{

    /** @var string */
    protected $lang;

    /** @var string */
    protected $scope;

    /** @var int */
    protected $return_code;

    /** @var string */
    protected $return_message;

    /** @var string */
    protected $id_ship;

    /** @var Shipment */
    protected $shipment;

    /**
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @param string $lang
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
    }

    /**
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param string $scope
     */
    public function setScope($scope)
    {
        $this->scope = $scope;
    }

    /**
     * @return int
     */
    public function getReturnCode()
    {
        return $this->return_code;
    }

    /**
     * @param int $return_code
     */
    public function setReturnCode($return_code)
    {
        $this->return_code = $return_code;
    }

    /**
     * @return string
     */
    public function getReturnMessage()
    {
        return $this->return_message;
    }

    /**
     * @param $return_message
     */
    public function setReturnMessage($return_message)
    {
        $this->return_message = $return_message;
    }

    /**
     * @return string
     */
    public function getIdShip()
    {
        return $this->id_ship;
    }

    /**
     * @param string $id_ship
     */
    public function setIdShip($id_ship)
    {
        $this->id_ship = $id_ship;
    }

    /**
     * @return Shipment
     */
    public function getShipment()
    {
        return $this->shipment;
    }

    /**
     * @param array $data
     */
    public function setShipment($data)
    {
        $shipment = new Shipment();
        foreach ($data as $parameter => $value) {
            $shipment->{'set'.$parameter}($value);
        }

        $this->shipment = $shipment;
    }

    /**
     * @return bool
     */
    public function hasError()
    {
        return !in_array($this->return_code, [200, 207]);
    }

    /**
     * @return bool
     */
    public function isDelivered()
    {
        return $this->shipment->isIsFinal();
    }

    /**
     * @return string
     */
    public function getTrackingUrl()
    {
        return sprintf(
            'https://www.laposte.fr/particulier/outils/suivre-vos-envois?code=%s',
            $this->shipment->getIdShip()
        );
    }

    /**
     * @return bool|DateTime
     */
    public function getPlannedDeliveryDate()
    {
        foreach ($this->shipment->getTimeline() as $timeline) {
            if ($timeline->getId() == 5 && $timeline->getDate() instanceof DateTime) {
                return $timeline->getDate();
            }
        }

        return false;
    }

    /**
     * @return Event
     */
    public function getCurrentEvent()
    {
        return reset($this->shipment->getEvent());
    }
}
