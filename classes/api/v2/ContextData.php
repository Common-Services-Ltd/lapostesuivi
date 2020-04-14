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
 * Class ContextData
 */
class ContextData
{

    /** @var int[] */
    protected $delivery_choice;

    /** @var array */
    protected $removal_point;

    /** @var string */
    protected $origin_country;

    /** @var string */
    protected $arrival_country;

    /** @var array */
    protected $partner;

    /**
     * @return int[]
     */
    public function getDeliveryChoice()
    {
        return $this->delivery_choice;
    }

    /**
     * @param int[] $delivery_choice
     */
    public function setDeliveryChoice($delivery_choice)
    {
        $this->delivery_choice = $delivery_choice;
    }

    /**
     * @return array
     */
    public function getRemovalPoint()
    {
        return $this->removal_point;
    }

    /**
     * @param array $removal_oint
     */
    public function setRemovalPoint($removal_oint)
    {
        $this->removal_point = $removal_oint;
    }

    /**
     * @return string
     */
    public function getOriginCountry()
    {
        return $this->origin_country;
    }

    /**
     * @param string $origin_country
     */
    public function setOriginCountry($origin_country)
    {
        $this->origin_country = $origin_country;
    }

    /**
     * @return string
     */
    public function getArrivalCountry()
    {
        return $this->arrival_country;
    }

    /**
     * @param string $arrival_country
     */
    public function setArrivalCountry($arrival_country)
    {
        $this->arrival_country = $arrival_country;
    }

    /**
     * @return array
     */
    public function getPartner()
    {
        return $this->partner;
    }

    /**
     * @param array $partner
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;
    }
}
