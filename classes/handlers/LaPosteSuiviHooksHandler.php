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
 * Class LaPosteSuiviHooksHandler
 */
class LaPosteSuiviHooksHandler implements LaPosteSuiviConstantInterface
{

    /** @var LaPosteSuivi */
    protected $module;

    /** @var array */
    protected $hooks = array(
        'actionAdminOrdersListingFieldsModifier',
        'displayAdminOrder',
        'displayOrderDetail'
    );

    /**
     * LaPosteSuiviHooksHandler constructor.
     *
     * @param LaPosteSuivi $module
     */
    public function __construct(LaPosteSuivi $module)
    {
        $this->module = $module;
    }

    /**
     * @param string $string
     * @return string
     */
    public function l($string)
    {
        return $this->module->l($string, basename(__FILE__, '.php'));
    }

    /**
     * @return array
     */
    public function getHooks()
    {
        return $this->hooks;
    }

    /**
     * @param string $action
     * @return bool
     */
    public function setHooks($action = self::UPDATE)
    {
        if (in_array($action, array(self::REMOVE, self::UPDATE))) {
            foreach ($this->hooks as $expected_hook) {
                $this->module->unregisterHook($expected_hook);
            }
        }

        $pass = true;

        try {
            if (in_array($action, array(self::ADD, self::UPDATE))) {
                foreach ($this->hooks as $expected_hook) {
                    $pass &= $this->module->registerHook($expected_hook);
                }
            }
        } catch (PrestaShopException $prestashop_exception) {
            $this->module->addErrors(sprintf(
                '%s [%s] : %s',
                basename(__FILE__),
                __LINE__,
                $prestashop_exception->getMessage()
            ));

            return false;
        }

        return $pass;
    }

    /**
     * @param array $params
     */
    public function actionAdminOrdersListingFieldsModifier($params)
    {
        if (!isset($params['fields']['tracking_number']) || !isset($params['fields']['shipping_number'])) {
            $params['fields']['tracking_number'] = array(
                'title' => $this->l('Tracking number'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
            );
        }

        if (isset($params['select']) && !$this->trackingNumberAlreadyInSelect($params['select'])) {
            $params['select'] = trim($params['select'], ', ').',
                IF(a.`shipping_number`, a.`shipping_number`, oc.`tracking_number`) AS `tracking_number`';
        }

        if (isset($params['join']) && !$this->trackingNumberAlreadyInJoin($params['join'])) {
            $params['join'] .= '
                LEFT JOIN `'._DB_PREFIX_.'order_carrier` oc ON (a.`id_order` = oc.`id_order`)';
        }
    }

    /**
     * Check if clause concerning the tracking number already exists
     * as SoNice modules implement this hook too.
     *
     * @param string $select
     * @return bool
     */
    private function trackingNumberAlreadyInSelect($select)
    {
        $keywords = array(
            'IF(a.`shipping_number`, a.`shipping_number`, oc.`tracking_number`) AS `tracking_number`',
            'AS `tracking_number`',
            'AS tracking_number'
        );

        foreach ($keywords as $keyword) {
            if (strpos($select, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a join concerning the OrderCarrier table already exists
     * as SoNice modules implement this hook too.
     *
     * @param string $join
     * @return bool
     */
    private function trackingNumberAlreadyInJoin($join)
    {
        $keywords = array(
            _DB_PREFIX_.'order_carrier',
            'oc ON',
            '= oc.`id_order`'
        );

        foreach ($keywords as $keyword) {
            if (strpos($join, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Can be used by modules when the tab AdminOrder is displayed in the Back Office.
     *
     * @param array $params
     * @return string
     * @throws Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function displayAdminOrder($params)
    {
        $success = true;

        $order = new LaPosteSuiviOrder((int)$params['id_order']);
        $success &= Validate::isLoadedObject($order);

        $carrier = new Carrier($order->id_carrier);
        $success &= Validate::isLoadedObject($carrier);
        $success &= LaPosteSuiviTools::isCarrierSelected($carrier);

        $tracking_number = $order->getOrderTrackingNumber();

        if ($tracking_number && $success) {
            $tracking = LaPosteSuiviWebService::getInstanceFromTrackingNumber($tracking_number);

            if ($tracking->hasBeenTrackedDuringTheLastXHours()) {
                $tracking->call()->save();
                $tracking = LaPosteSuiviWebService::getInstanceFromTrackingNumber($tracking_number);
            }

            if (Validate::isLoadedObject($tracking)) {
                $tracking->status = $this->module->status_code[$tracking->status];
            }
        } else {
            $tracking = new LaPosteSuiviWebService();
        }

        Context::getContext()->smarty->assign('lps_tracking', $tracking);

        Context::getContext()->controller->addCSS($this->module->folder_path.'/views/css/back.css');

        return Context::getContext()->smarty->fetch(
            $this->module->folder_path.'views/templates/admin/admin_order.tpl'
        );
    }

    /**
     * Displayed on order detail on front office.
     *
     * @param array $params
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function displayOrderDetail($params)
    {
        $order = new LaPosteSuiviOrder($params['order']->id);

        $tracking_number = $order->getOrderTrackingNumber();
        $tracking = LaPosteSuiviWebService::getInstanceFromTrackingNumber($tracking_number);

        if (!Validate::isLoadedObject($tracking)) {
            return false;
        }

        $tracking->date = date('d-m-Y', strtotime($tracking->date));

        Context::getContext()->smarty->assign('lps_tracking', $tracking);

        return Context::getContext()->smarty->fetch(
            $this->module->folder_path.'views/templates/admin/order_details.tpl'
        );
    }
}
