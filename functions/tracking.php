<?php
/**
 * @author    debuss-a <alexandre@common-services.com>
 * @copyright Copyright (c) 2018 Common-Services
 * @license   CC BY-SA 4.0
 */

if (isset($_SERVER['DropBox']) && $_SERVER['DropBox']) {
    // Development environment
    require_once readlink(dirname($_SERVER['SCRIPT_FILENAME']).'/../../../config/config.inc.php');
    require_once readlink(_PS_MODULE_DIR_.'lapostesuivi/bootstrap/autoload.php');
} else {
    require_once dirname(__FILE__).'/../../../config/config.inc.php';
    require_once _PS_MODULE_DIR_.'lapostesuivi/bootstrap/autoload.php';
}

echo 'Tracking started on : '.date('c').'<br>';
echo '====================================<br><br><br>';

if (Tools::getValue('token') != Configuration::get('LPS_TOKEN')) {
    die('Wrong token, tracking is therefore not allowed !');
}

LaPosteSuiviTools::cleanOldEntryInDb();

$selected_statuses = Tools::unSerialize(Configuration::get('LPS_SELECTED_STATUS'));
if (!is_array($selected_statuses) || !count($selected_statuses)) {
    die('Tracking Cancelled : Module configuration is not complete, statuses need to be set.');
}

try {
    foreach (LaPosteSuiviWebService::callMultiple()->getResponse() as $id_order => $response) {
        if (!is_array($response) || !count($response)) {
            echo '<span style="color: orange;">';
            echo '> No orders to track...';
            echo '</span><br><br><br>';
            continue;
        }

        echo '> Treating Order #'.$id_order.'<br>';

        if (array_key_exists('error', $response)) {
            echo '<span style="color: red;">';
            echo sprintf(
                '> %s : %s',
                $response['error']['code'],
                $response['error']['message']
            );
            echo '</span><br><br><br>';
            continue;
        } elseif (!array_key_exists('data', $response)) {
            echo '> ERROR : Empty response from web service, tracking is therefore skipped.';
            echo '</span><br><br><br>';
            continue;
        }

        $response = $response['data'];
        $shipping_number = $response['code'];

        echo '> Shipping Number : '.$shipping_number.'<br>';

        echo '<span style="color: green;">> SUCCESS !<br>';
        echo '=> On : '.$response['date'].'<br>';
        echo '=> Status : '.$response['status'].'<br>';
        echo '=> Message : '.$response['message'].'</span><br>';

        $tracking = LaPosteSuiviWebService::getInstanceFromTrackingNumber($shipping_number);

        foreach ($response as $key => $val) {
            $tracking->{$key} = $val;
        }
        $tracking->type = 'Colis';

        $tracking->save();

        foreach ($selected_statuses as $id_order_state => $status) {
            if (in_array($tracking->status, $status)) {
                $order = new LaPosteSuiviOrder($id_order);
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

                if (!Validate::isLoadedObject($order)) {
                    echo '> Unable to load order, therefore order status cannot be changed.<br>';
                } elseif ($order->current_state == $id_order_state) {
                    echo '> Order has already the correct status, nothing to do.<br>';
                } elseif (!$order->setCurrentState($id_order_state, $employee->id)) {
                    echo '<span style="color: red;">> ERROR : Unable to change order status...</span><br>';
                } else {
                    echo '<span style="color: green;">> SUCCESS : order status has been changed.</span><br>';
                }

                break;
            }
        }

        echo '<br><br><br>';
    }
} catch (Exception $e) {
    echo '<span style="color: red;">> FATAL ERROR : '.$e->getMessage().'</span><br><br><br>';
}

echo '===================================<br>';
echo 'Tracking ended on : '.date('c');
