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
 * Class LaPosteSuiviTools
 */
class LaPosteSuiviTools
{

    /**
     * Implementation of array_column, only available since PHP 5.5.0.
     *
     * @see http://php.net/manual/en/function.array-column.php
     * @param array $array
     * @param mixed $column_name
     * @return array
     */
    public static function arrayColumn($array, $column_name)
    {
        if (function_exists('array_column')) {
            return array_column($array, $column_name);
        }

        return array_map(
            array(__CLASS__, 'arrayColumnCallback'),
            $array,
            array_fill(0, count($array), $column_name)
        );
    }

    /**
     * @param array $element
     * @param mixed $column_name
     * @return mixed
     */
    private static function arrayColumnCallback($element, $column_name)
    {
        return $element[$column_name];
    }

    /**
     * @return string
     */
    public static function generateRandomPassword()
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        $password = Tools::substr(str_shuffle($chars), 0, 8);
        $password .= Tools::substr(str_shuffle($chars), 0, 8);
        $password .= Tools::substr(str_shuffle($chars), 0, 8);
        $password .= Tools::substr(str_shuffle($chars), 0, 8);

        return $password;
    }

    /**
     * When the tracking range is reached, it starts over, which means old tracking number will be used again for new
     * orders. Then no need to keep them, deleted after 2 month.
     *
     * @return bool
     */
    public static function cleanOldEntryInDb()
    {
        return Db::getInstance()->delete(
            'lapostesuivi',
            '`date` < DATE_ADD(NOW(), INTERVAL - 94 DAY)'
        );
    }

    /**
     * @param Carrier $carrier
     * @return bool
     */
    public static function isCarrierSelected(Carrier $carrier)
    {
        $selected_carriers_references = (array)Tools::unSerialize(Configuration::get('LPS_SELECTED_CARRIERS'));
        if (!is_array($selected_carriers_references) || !count($selected_carriers_references)) {
            return false;
        }

        return array_search($carrier->id_reference, $selected_carriers_references) !== false;
    }

    /**
     * Currently active overrides with path.
     *
     * @return array
     */
    public static function getOverrides()
    {
        $overrides = array();

        if (!Configuration::get('PS_DISABLE_OVERRIDES') && is_dir(_PS_OVERRIDE_DIR_)) {
            $recursive_director_iterator = new RecursiveDirectoryIterator(_PS_OVERRIDE_DIR_);
            $recursive_iterator_iterator = new RecursiveIteratorIterator(
                $recursive_director_iterator,
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($recursive_iterator_iterator as $file) {
                /** @var $file SplFileInfo */
                if ($file->isDir() || $file->getExtension() != 'php' || $file->getBasename() == 'index.php') {
                    continue;
                }

                $overrides[] = str_replace(array(_PS_ROOT_DIR_, '\\'), array('', '/'), $file->getRealpath());
            }
        }

        return $overrides;
    }
}
