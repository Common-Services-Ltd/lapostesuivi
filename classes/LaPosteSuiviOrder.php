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
 * Class LaPosteSuiviOrder
 */
class LaPosteSuiviOrder extends Order
{

    /**
     * Exactly the same as Order::setCurrentState() except that we return the
     * result of OrderHistory::addWithemail().
     *
     * @param int $id_order_state
     * @param int $id_employee
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function setCurrentState($id_order_state, $id_employee = 0)
    {
        if (empty($id_order_state)) {
            return false;
        }

        $history = new OrderHistory();
        $history->id_order = (int)$this->id;
        $history->id_employee = (int)$id_employee;
        $history->changeIdOrderState((int)$id_order_state, $this);

        $res = Db::getInstance()->getRow(
            'SELECT `invoice_number`, `invoice_date`, `delivery_number`, `delivery_date`
			FROM `'._DB_PREFIX_.'orders`
			WHERE `id_order` = '.(int)$this->id
        );

        $this->invoice_date = $res['invoice_date'];
        $this->invoice_number = $res['invoice_number'];
        $this->delivery_date = $res['delivery_date'];
        $this->delivery_number = $res['delivery_number'];

        $this->update();

        return $history->addWithemail();
    }

    /**
     * @return string|null
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function getOrderTrackingNumber()
    {
        if (isset($this->shipping_number) && !empty($this->shipping_number)) {
            return $this->shipping_number;
        }

        $id_order_carrier = (int)Db::getInstance()->getValue(
            'SELECT `id_order_carrier`
            FROM `'._DB_PREFIX_.'order_carrier`
            WHERE `id_order` = '.(int)$this->id.'
            ORDER BY `id_order_carrier` DESC'
        );

        $order_carrier = new OrderCarrier($id_order_carrier);
        if (Validate::isLoadedObject($order_carrier) && $order_carrier->tracking_number) {
            return $order_carrier->tracking_number;
        }

        return null;
    }
}
