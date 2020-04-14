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
 * Class Event
 */
class Event
{

    /** @var int */
    protected $order;

    /** @var string */
    protected $label;

    /** @var DateTime */
    protected $date;

    /** @var string */
    protected $code;

    /**
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param int $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param string $date
     * @throws Exception
     */
    public function setDate($date)
    {
        if (is_string($date) && strlen($date)) {
            $date = new DateTime($date);
        }

        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }
}
