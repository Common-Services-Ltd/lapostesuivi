<?php
/**
 * @author    debuss-a <alexandre@common-services.com>
 * @copyright Copyright (c) 2018 Common-Services
 * @license   CC BY-SA 4.0
 */

$file = new SplFileInfo($_SERVER['SCRIPT_FILENAME']);

require_once dirname(dirname(dirname($file->getPath()))).'/config/config.inc.php';
require_once dirname(__FILE__).'/../bootstrap/autoload.php';

echo 'Tracking started on : '.date('c').'<br>';
echo '====================================<br><br><br>';

if (Tools::getValue('token') != Configuration::get('LPS_TOKEN')) {
    die('Wrong token, tracking is therefore not allowed !');
}

LaPosteSuiviTools::cleanOldEntryInDb();

$id_order_state_shipped = (int)Configuration::get('LPS_SHIPPED_ORDER_STATE');
$id_order_state_delivered = (int)Configuration::get('LPS_DELIVERED_ORDER_STATE');
if (!$id_order_state_shipped || !$id_order_state_delivered) {
    die('Tracking Cancelled : Module configuration is not complete, statuses need to be set.');
}

$employee = new Employee((int)Configuration::get('LPS_EMPLOYEE_ID'));
if (!Validate::isLoadedObject($employee)) {
    // If invalid employee then fetch any active employee
    $employee = new Employee((int)Db::getInstance()->getValue(
        'SELECT `id_employee`
            FROM `'._DB_PREFIX_.'employee`
            WHERE `active` = 1
            ORDER BY `id_employee`'
    ));
}

try {
    foreach (LaPosteSuiviWebService::callMultipleV2() as $id_order => $response) {
        echo '> Treating Order #'.$id_order.'<br>';

        if ($response->getReturnCode() == 400) {
            echo '<span style="color: red;">';
            echo sprintf(
                '> %s : %s',
                400,
                $response->getReturnMessage()
            );
            echo '</span><br><br><br>';
            continue;
        } elseif (!count($response->getShipment()->getEvent())) {
            echo '> ERROR : Empty events in response from web service, tracking is therefore skipped.';
            echo '</span><br><br><br>';
            continue;
        }

        $event = $response->getShipment()->getEvent()[0];
        $shipping_number = $response->getIdShip();

        echo '> Shipping Number : '.$shipping_number.'<br>';

        echo '<span style="color: green;">> SUCCESS !<br>';
        echo '=> On : '.$event->getDate()->format('d/m/Y').'<br>';
        echo '=> Status : '.$event->getCode().'<br>';
        echo '=> Message : '.$event->getLabel().'</span><br>';

        $tracking = LaPosteSuiviWebService::getInstanceFromTrackingNumber($shipping_number);

        $tracking->code = $shipping_number;
        $tracking->type = 'Colis';
        $tracking->status = $event->getCode();
        $tracking->message = $event->getLabel();
        $tracking->link = $response->getShipment()->getUrlDetail();
        $tracking->date = $event->getDate()->format('Y-m-d');
        $tracking->save();

        $order = new LaPosteSuiviOrder($id_order);
        if (!Validate::isLoadedObject($order)) {
            echo '> Unable to load order, therefore order status cannot be changed.<br>';
        } elseif ($response->isDelivered() && $order->current_state != $id_order_state_delivered) {
            if (!$order->setCurrentState($id_order_state_delivered, $employee->id)) {
                echo '<span style="color: red;">> ERROR : Unable to change order status to DELIVERED...</span><br>';
            } else {
                echo '<span style="color: green;">> SUCCESS : order status has been changed to DELIVERED.</span><br>';
            }
        } elseif ($order->current_state != $id_order_state_shipped) {
            if (!$order->setCurrentState($id_order_state_shipped, $employee->id)) {
                echo '<span style="color: red;">> ERROR : Unable to change order status to SHIPPED...</span><br>';
            } else {
                echo '<span style="color: green;">> SUCCESS : order status has been changed to SHIPPED.</span><br>';
            }
        }

        echo '<br><br><br>';
    }
} catch (Exception $e) {
    echo '<span style="color: red;">> FATAL ERROR : '.$e->getMessage().'</span><br><br><br>';
}

echo '===================================<br>';
echo 'Tracking ended on : '.date('c');
