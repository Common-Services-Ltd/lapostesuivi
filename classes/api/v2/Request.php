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
 * Class Request
 */
class Request
{

    /** @var string */
    protected $id;

    /** @var string */
    protected $lang;

    /** @var string */
    protected $ip_address;

    /**
     * Request constructor.
     *
     * @param string $id
     * @param string $lang
     * @param string $ip_address
     * @throws InvalidArgumentException
     */
    public function __construct($id, $lang = 'fr_FR', $ip_address = null)
    {
        if (!is_string($id) || strlen($id) < 11 || strlen($id) > 15) {
            throw new InvalidArgumentException('Tracking number (id) must be a string of 11, up to 15, alphanumeric characters.');
        }

        $allowed_lang_enum = ['fr_FR', 'de_DE', 'en_GB', 'es_ES', 'it_IT', 'nl_NL'];

        if (!in_array($lang, $allowed_lang_enum)) {
            throw new InvalidArgumentException(sprintf(
                'Response language must be one of the following : %s.',
                implode(', ', $allowed_lang_enum)
            ));
        }

        $this->id = $id;
        $this->lang = $lang;
        $this->ip_address = filter_var($ip_address, FILTER_VALIDATE_IP) ?:
            filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP) ?:
                '123.123.123.123';
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

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
    public function getIpAddress()
    {
        return $this->ip_address;
    }

    /**
     * @param string $ip_address
     */
    public function setIpAddress($ip_address)
    {
        $this->ip_address = $ip_address;
    }
}
