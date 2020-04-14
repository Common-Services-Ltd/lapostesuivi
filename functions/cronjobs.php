<?php
/**
 * @author    debuss-a <alexandre@common-services.com>
 * @copyright Copyright (c) 2018 Common-Services
 * @license   CC BY-SA 4.0
 */

$file = new SplFileInfo($_SERVER['SCRIPT_FILENAME']);

require_once dirname(dirname(dirname($file->getPath()))).'/config/config.inc.php';
require_once dirname(__FILE__).'/../bootstrap/autoload.php';

ob_start();

if (Tools::getValue('token') != Configuration::get('LPS_TOKEN')) {
    die('Wrong token...');
} elseif (!Tools::getValue('url')) {
    die('Unable to get the cronjob URL...');
}

$id_shop = (int)Context::getContext()->shop->id;
$id_shop_group = (int)Context::getContext()->shop->id_shop_group;

// Delete previous saved cron
Db::getInstance()->execute(
    'DELETE FROM `'._DB_PREFIX_.'cronjobs`
    WHERE `task` LIKE "%'.urlencode('/lapostesuivi/').'%"
    AND `id_shop` = '.(int)$id_shop
);

$success = true;
$url = Tools::getValue('url');
// Every 4h
$hours = array(0, 4, 8, 12, 16, 20);

foreach ($hours as $hour) {
    $success &= Db::getInstance()->execute(
        'INSERT INTO '._DB_PREFIX_.'cronjobs (
                `description`,
                `task`,
                `hour`,
                `day`,
                `month`,
                `day_of_week`,
                `updated_at`,
                `one_shot`,
                `active`,
                `id_shop`,
                `id_shop_group`
            ) VALUES (
                "'.pSQL('La Poste Suivi').'",
                "'.pSQL(urlencode($url)).'",
                '.pSQL($hour).',
                '.pSQL('-1').',
                '.pSQL('-1').',
                '.pSQL('-1').',
                NULL,
                FALSE,
                TRUE,
                '.$id_shop.',
                '.$id_shop_group.'
            )'
    );
}

die(Tools::jsonEncode(array(
    'success' => $success,
    'message' => ob_get_clean()
)));
