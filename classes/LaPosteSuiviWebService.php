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
 * Class LaPosteSuiviWebService
 */
class LaPosteSuiviWebService extends ObjectModel
{

    const ENDPOINT = 'https://api.laposte.fr';

    const USER_AGENT = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1';

    const LANG_FR = 'fr_FR';
    const LANG_DE = 'de_DE';
    const LANG_EN = 'en_GB';
    const LANG_ES = 'es_ES';
    const LANG_IT = 'it_IT';
    const LANG_NL = 'nl_NL';

    public $id_lapostesuivi;
    public $code;
    public $type;
    public $status;
    public $message;
    public $link;
    public $date;
    public $date_add;
    public $date_upd;

    public $tracking_number;

    protected $product = 'suivi';
    protected $version = 'v1';
    protected $response;
    protected $response_code;

    public static $definition = array(
        'table' => 'lapostesuivi',
        'primary' => 'id_lapostesuivi',
        'fields' => array(
            'code' => array('type' => self::TYPE_STRING),
            'type' => array('type' => self::TYPE_STRING),
            'status' => array('type' => self::TYPE_STRING),
            'message' => array('type' => self::TYPE_STRING),
            'link' => array('type' => self::TYPE_STRING),
            'date' => array('type' => self::TYPE_STRING),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false),
        )
    );

    /**
     * LaPosteSuiviWebService constructor.
     *
     * @param null $id_lapostesuivi
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function __construct($id_lapostesuivi = null)
    {
        parent::__construct($id_lapostesuivi);
    }

    /**
     * @param string $tracking_number
     * @return LaPosteSuiviWebService
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function getInstanceFromTrackingNumber($tracking_number)
    {
        $id_lapostesuivi = (int)Db::getInstance()->getValue(
            'SELECT `id_lapostesuivi`
            FROM `'._DB_PREFIX_.'lapostesuivi`
            WHERE `code` = "'.pSQL($tracking_number).'"'
        );

        if ($id_lapostesuivi) {
            return new self($id_lapostesuivi);
        }

        $tracking = new self();
        $tracking->code = $tracking_number;

        return $tracking;
    }

    /* -----------------------------------------------------------------------------------------------------------------
     | API V2
     ---------------------------------------------------------------------------------------------------------------- */

    /**
     * @param Request $request
     * @return Response
     * @throws InvalidArgumentException
     * @throws PrestaShopDatabaseException
     * @throws Exception
     */
    public function callV2($request)
    {
        return reset($this->callMultipleV2([$request]));
    }

    /**
     * @param Request[]|null $requests
     * @return Response[]
     * @throws InvalidArgumentException
     * @throws PrestaShopDatabaseException
     * @throws Exception
     */
    public static function callMultipleV2($requests = null)
    {
        $x_okapi_key = Configuration::get('LPS_X_OKAPI_KEY');
        if (!$x_okapi_key) {
            throw new Exception('Missing X-Okapi-Key.');
        } elseif (Tools::strlen($x_okapi_key) !== 64) {
            throw new Exception('Wrong X-Okapi-Key, it is supposed to be 64 characters long.');
        }

        if (!$requests || !is_array($requests)) {
            $requests = array(
                1 => new Request('6W111111111XX')
            );

            foreach (LaPosteSuiviWebService::getOrdersToTrack() as $id_order => $tracking) {
                $requests[$id_order] = new Request($tracking);
            }
        }

        $multi_curl = array();
        $results = array();

        $curl = curl_multi_init();

        foreach ($requests as $id => $request) {
            $multi_curl[$id] = curl_init();

            curl_setopt($multi_curl[$id], CURLOPT_USERAGENT, self::USER_AGENT);
            curl_setopt($multi_curl[$id], CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($multi_curl[$id], CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($multi_curl[$id], CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($multi_curl[$id], CURLOPT_URL, sprintf(
                '%s/suivi/v2/idships/%s?lang=%s',
                self::ENDPOINT,
                $request->getId(),
                $request->getLang()
            ));
            curl_setopt($multi_curl[$id], CURLOPT_HTTPHEADER, array(
                'X-Okapi-Key: '.$x_okapi_key,
                'Accept: application/json',
                'X-Forwarded-For: '.$request->getIpAddress()
            ));

            curl_multi_add_handle($curl, $multi_curl[$id]);
        }

        $index = null;
        do {
            curl_multi_exec($curl, $index);
        } while ($index > 0);

        foreach ($multi_curl as $key => $ch) {
            $results[$key] = curl_multi_getcontent($ch);

            curl_multi_remove_handle($curl, $ch);
        }

        curl_multi_close($curl);

        $responses = array();

        foreach ($results as $id_order => $result) {
            $result = json_decode($result, true);
            if ($result === null) {
                throw new Exception('Unable to json_decode response from the API.');
            }

            $response = new Response();

            foreach ($result as $parameter => $value) {
                $response->{'set'.ucfirst($parameter)}($value);
            }

            if (!$response->getIdShip()) {
                $response->setIdShip($response->getShipment()->getIdShip());
            }

            $responses[$id_order] = $response;
        }

        return $responses;
    }

    /* -----------------------------------------------------------------------------------------------------------------
     | API V1
     ---------------------------------------------------------------------------------------------------------------- */

    /**
     * @return string Web service URI according to product and version
     * @throws Exception
     */
    protected function getUri()
    {
        if (is_array($this->code) && !count($this->code) ||
            !is_array($this->code) && Tools::strlen($this->code) < 9) {
            throw new Exception('No orders to track.');
        }

        return sprintf(
            '%s/%s/%s/%s',
            self::ENDPOINT,
            $this->product,
            $this->version,
            is_array($this->code) ?
                'list?codes='.implode(',', $this->code) : $this->code
        );
    }

    /**
     * @return $this
     * @throws Exception
     */
    public function call()
    {
        $x_okapi_key = Configuration::get('LPS_X_OKAPI_KEY');

        if (!$x_okapi_key) {
            throw new Exception('Missing X-Okapi-Key.');
        } elseif (Tools::strlen($x_okapi_key) !== 64) {
            throw new Exception('Wrong X-Okapi-Key, it is supposed to be 64 characters long.');
        }

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_USERAGENT, self::USER_AGENT);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_URL, $this->getUri());
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'X-Okapi-Key: '.$x_okapi_key
        ));

        $this->response = Tools::jsonDecode(curl_exec($curl), 1);
        $this->response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        return $this;
    }

    /**
     * @param array $codes A list of tracking numbers
     * @return LaPosteSuiviWebService
     * @throws Exception
     */
    public static function callMultiple($codes = null)
    {
        if (!$codes || !is_array($codes)) {
            $codes = LaPosteSuiviWebService::getOrdersToTrack();
        }

        $code_chunks = array_chunk($codes, 25, true);

        $tracking = new self();
        foreach ($code_chunks as $chunk) {
            $temp_tracking = new self();
            $temp_tracking->code = (array)$chunk;
            $temp_tracking->call();

            $tracking->code = (array)$tracking->code + $temp_tracking->code;
            $tracking->response = array_merge((array)$tracking->response, $temp_tracking->response);
        }

        return $tracking;
    }

    /**
     * @return array
     */
    public function getResponse()
    {
        if (is_array($this->code) && count($this->code) && is_array($this->response) && count($this->response)) {
            $this->response = array_combine(array_keys($this->code), $this->response);
        }

        return (array)$this->response;
    }

    /**
     * @return int
     */
    public function getResponseCode()
    {
        return $this->response_code;
    }

    /**
     * Check if an order has been tracked within a certain range of time.
     *
     * @param int $hours
     * @return int
     */
    public function hasBeenTrackedDuringTheLastXHours($hours = 4)
    {
        return (int)Db::getInstance()->getValue(
            'SELECT `id_lapostesuivi`
            FROM `'._DB_PREFIX_.'lapostesuivi`
            WHERE `code` = "'.pSQL($this->code).'"
            AND `date_upd` < DATE_ADD(NOW(), INTERVAL - '.max(1, (int)$hours).' HOUR)'
        );
    }

    /**
     * @return array
     * @throws PrestaShopDatabaseException
     */
    public static function getOrdersToTrack()
    {
        $carriers_references = Tools::unSerialize(Configuration::get('LPS_SELECTED_CARRIERS'));
        $deadline = Configuration::get('LPS_TRACKING_DEADLINE');

        if (!is_array($carriers_references) || !count($carriers_references)) {
            return array();
        }

        $carriers_ids = LaPosteSuiviTools::arrayColumn(Db::getInstance()->executeS(
            'SELECT `id_carrier`
            FROM `'._DB_PREFIX_.'carrier`
            WHERE `id_reference`  IN ('.implode(', ', array_map('intval', $carriers_references)).')'
        ), 'id_carrier');

        $orders_ids = Db::getInstance()->executeS(
            'SELECT `id_order`, `shipping_number`
            FROM `'._DB_PREFIX_.'orders`
            WHERE `id_carrier` IN ('.implode(', ', array_map('intval', $carriers_ids)).')
            AND `date_add` > DATE_ADD(NOW(), INTERVAL - '.max(7, (int)$deadline).' DAY)
            AND `shipping_number` IS NOT NULL
            AND `shipping_number` != ""
            ORDER BY `id_order`'
        );

        if (!is_array($orders_ids) || !count($orders_ids)) {
            return array();
        }

        return array_combine(
            LaPosteSuiviTools::arrayColumn($orders_ids, 'id_order'),
            array_map(function ($number) {
                return str_replace(' ', '', $number);
            }, LaPosteSuiviTools::arrayColumn($orders_ids, 'shipping_number'))
        );
    }

    /**
     * Old tracking URL is not working anymore.
     * If it is returned by the web service then replace it by the new one.
     */
    protected function fixTrackingLink()
    {
        if (strpos($this->link, 'http://www.colissimo.fr/portail_colissimo/suivre.do?colispart=') !== false) {
            $this->link = str_replace(
                'http://www.colissimo.fr/portail_colissimo/suivre.do?colispart=',
                'https://www.laposte.fr/particulier/outils/suivre-vos-envois?code=',
                $this->link
            );
        }
    }

    /**
     * @see ObjectModel::add()
     * @param bool $auto_date
     * @param bool $null_values
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function add($auto_date = true, $null_values = false)
    {
        $this->fixTrackingLink();

        return parent::add($auto_date, $null_values);
    }

    /**
     * @see ObjectModel::update()
     * @param bool $null_values
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function update($null_values = false)
    {
        $this->fixTrackingLink();

        return parent::update($null_values);
    }
}
