<?php
/**
 * @author    debuss-a <alexandre@common-services.com>
 * @copyright Copyright (c) 2018 Common-Services
 * @license   CC BY-SA 4.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__).'/../../bootstrap/autoload.php';

/**
 * Class AdminOrdersLaPosteSuivi
 */
class AdminOrdersLaPosteSuiviController extends ModuleAdminController
{

    /** @var LaPosteSuivi */
    public $module;

    /** @var array */
    protected $statuses_array = array();

    /** @var array */
    protected $codes_array = array();

    /** @var Context */
    protected $context;

    /**
     * AdminOrdersLaPosteSuiviController constructor.
     */
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'lapostesuivi';
        $this->className = __CLASS__;
        $this->context = Context::getContext();
        $this->lang = false;
        $this->allow_export = true;
        $this->deleted = false;
        $this->list_simple_header = false;

        $this->addRowAction('tracking');

        foreach (LaPosteSuiviOrderState::getOrderStatesShipped((int)$this->context->language->id) as $status) {
            $this->statuses_array[$status['id_order_state']] = $status['name'];
        }

        try {
            $codes = LaPosteSuiviTools::arrayColumn(
                Db::getInstance()->executeS(
                    'SELECT DISTINCT(`message`)
                    FROM `'._DB_PREFIX_.'lapostesuivi`'
                ),
                'message'
            );
        } catch (Exception $exception) {
            Tools::dieOrLog($exception->getMessage(), false);
            $codes = array();
        }
        $this->codes_array = array_combine($codes, $codes);

        $this->_select = '
            o.`id_order`,
            CONCAT(LEFT(cs.`firstname`, 1), ". ", cs.`lastname`) AS `customer`,
            cr.`name` as `carrier`,
            CONCAT(ad.`postcode`, " ", ad.`city`, ", ", cl.`name`) AS `address`,
            osl.`name` as `order_state`,
            os.`color`
        ';

        $this->_join = '
            LEFT JOIN `'._DB_PREFIX_.'orders` o ON o.`id_order` = (
                SELECT o.`id_order`
                FROM `'._DB_PREFIX_.'orders` o
                WHERE o.`shipping_number` = a.`code`
            )
            LEFT JOIN `'._DB_PREFIX_.'customer` cs ON cs.`id_customer` = (
                SELECT o.`id_customer`
                FROM `'._DB_PREFIX_.'orders` o
                WHERE o.`shipping_number` = a.`code`
            )
            LEFT JOIN `'._DB_PREFIX_.'carrier` cr ON cr.`id_carrier` = (
                SELECT o.`id_carrier`
                FROM `'._DB_PREFIX_.'orders` o
                WHERE o.`shipping_number` = a.`code`
            )
            LEFT JOIN `'._DB_PREFIX_.'address` ad ON ad.`id_address` = (
                SELECT o.`id_address_delivery`
                FROM `'._DB_PREFIX_.'orders` o
                WHERE o.`shipping_number` = a.`code`
            )
            LEFT JOIN `'._DB_PREFIX_.'country_lang` cl ON cl.`id_country` = ad.`id_country`
                AND cl.`id_lang` = '.(int)$this->context->language->id.'
            LEFT JOIN `'._DB_PREFIX_.'order_state_lang` osl ON osl.`id_order_state` = o.`current_state`
                AND osl.`id_lang` = '.(int)$this->context->language->id.'
            LEFT JOIN `'._DB_PREFIX_.'order_state` os ON os.`id_order_state` = o.`current_state`
        ';

        $this->_orderBy = 'id_lapostesuivi';
        $this->_orderWay = 'DESC';

        $this->module = Module::getInstanceByName($this->table);

        $this->fields_list = array(
            'id_order' => array(
                'title' => $this->l('ID'),
                'type'=> 'text',
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'remove_onclick' => true
            ),
            'date' => array(
                'title' => $this->l('Date'),
                'type'=> 'date',
                'orderby' => true,
                'search' => true,
                'remove_onclick' => true
            ),
            'customer' => array(
                'title' => $this->l('Customer'),
                'type'=> 'text',
                'orderby' => true,
                'search' => true,
                'havingFilter' => true,
                'remove_onclick' => true
            ),
            'carrier' => array(
                'title' => $this->l('Carrier'),
                'type'=> 'text',
                'orderby' => true,
                'search' => true,
                'havingFilter' => true,
                'remove_onclick' => true
            ),
            'address' => array(
                'title' => $this->l('Destination'),
                'type'=> 'text',
                'orderby' => true,
                'search' => true,
                'havingFilter' => true,
                'remove_onclick' => true
            ),
            'order_state' => array(
                'title' => $this->l('Status'),
                'type'=> 'select',
                'color' => 'color',
                'list' => $this->statuses_array,
                'filter_key' => 'osl!id_order_state',
                'filter_type' => 'int',
                'orderby' => true,
                'remove_onclick' => true
            ),
            'code' => array(
                'title' => $this->l('Tracking Number'),
                'type'=> 'text',
                'orderby' => false,
                'search' => true,
                'remove_onclick' => true
            ),
            'message' => array(
                'title' => $this->l('Message'),
                'type'=> 'select',
                'list' => $this->codes_array,
                'filter_key' => 'a!message',
                'orderby' => false,
                'remove_onclick' => true
            )
        );

        try {
            parent::__construct();
        } catch (PrestaShopException $prestashop_exception) {
            Tools::dieOrLog($prestashop_exception->getMessage(), false);
        }
    }

    /**
     * Uses the module instance translation tools instead of the AdminController one as the new translation system is
     * not working on PS 1.7.
     *
     * @param string $string
     * @param null $class
     * @param bool $addslashes
     * @param bool $htmlentities
     * @return string
     */
    public function l($string, $class = null, $addslashes = false, $htmlentities = true)
    {
        unset($class, $addslashes, $htmlentities);

        return $this->module->l($string, basename(__FILE__, '.php'));
    }

    /**
     * @see ControllerCore::setMedia()
     */
    public function setMedia()
    {
        parent::setMedia();

        $this->addCSS($this->module->folder_path.'/views/css/controller.css');
    }

    /**
     * Add a tracking <a> button instead of displaying the full URL.
     *
     * @param string|null $token
     * @param int $id
     * @param string|null $name
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function displayTrackingLink($token, $id, $name)
    {
        unset($token, $name);

        $this->context->smarty->assign('lps', new LaPosteSuiviWebService($id));

        return $this->context->smarty->fetch(
            $this->getTemplatePath().'controller/track_button.tpl'
        );
    }
}
