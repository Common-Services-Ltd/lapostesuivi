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
 * Class LaPosteSuiviOrderState
 */
class LaPosteSuiviOrderState extends OrderState
{

    /**
     * Only returns order states marked as "shipped", suitable for tracking.
     *
     * @param int $id_lang
     * @return array
     */
    public static function getOrderStatesShipped($id_lang)
    {
        $order_states = array();

        foreach (OrderState::getOrderStates($id_lang) as $order_state) {
            if ($order_state['paid'] == 1 && $order_state['shipped'] == 1 && $order_state['template'] != 'refund') {
                $order_states[] = $order_state;
            }
        }

        return $order_states;
    }
}
