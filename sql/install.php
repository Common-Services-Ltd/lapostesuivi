<?php
/**
 * @author    debuss-a <alexandre@common-services.com>
 * @copyright Copyright (c) 2018 Common-Services
 * @license   CC BY-SA 4.0
 */

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'lapostesuivi` (
    `id_lapostesuivi` int(11) NOT NULL AUTO_INCREMENT,
    `code` VARCHAR(32) NOT NULL,
    `type` VARCHAR(32) NOT NULL,
    `status` VARCHAR(16) DEFAULT NULL,
    `message` VARCHAR(256) DEFAULT NULL,
    `link` VARCHAR(256) DEFAULT NULL,
    `date` DATE NOT NULL,
    `date_add` DATETIME NOT NULL,
    `date_upd` DATETIME NOT NULL,
    PRIMARY KEY  (`id_lapostesuivi`),
    UNIQUE KEY `u_code` (`code`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (!Db::getInstance()->execute($query)) {
        return false;
    }
}
