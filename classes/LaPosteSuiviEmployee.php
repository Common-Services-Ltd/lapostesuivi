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
 * Class LaPosteSuiviEmployee
 */
class LaPosteSuiviEmployee extends Employee implements LaPosteSuiviConstantInterface
{

    /**
     * Create the cron task employee and return its ID, or false on error.
     *
     * @return bool|int
     */
    public static function create()
    {
        $id_employee = (int)Configuration::get('LPS_EMPLOYEE_ID');
        if (self::existsInDatabase($id_employee, self::$definition['table'])) {
            return $id_employee;
        }

        try {
            $cron_employee = new self();
            $cron_employee->id_profile = 1;
            $cron_employee->id_lang = Configuration::get('PS_LANG_DEFAULT');
            $cron_employee->lastname = 'Suivi';
            $cron_employee->firstname = 'LaPoste';
            $cron_employee->email = 'no-reply-'.(int)rand(1000, 9999).'@lapostesuivi.fr';
            $cron_employee->passwd = LaPosteSuiviTools::generateRandomPassword();
            $cron_employee->add();
        } catch (Exception $exception) {
            Tools::dieOrLog($exception->getMessage(), false);
            return false;
        }

        Configuration::updateValue('LPS_EMPLOYEE_ID', $cron_employee->id);

        return $cron_employee->id;
    }

    /**
     * @return bool
     */
    public static function remove()
    {
        $id_employee = (int)Configuration::get('LPS_EMPLOYEE_ID');
        if (!self::existsInDatabase($id_employee, self::$definition['table'])) {
            return true;
        }

        try {
            $cron_employee = new self($id_employee);
            if (Validate::isLoadedObject($cron_employee)) {
                return $cron_employee->delete();
            }
        } catch (Exception $exception) {
            Tools::dieOrLog($exception->getMessage(), false);
        }

        return false;
    }

    /**
     * Keep only 1 LaPoste Suivi employee in Db.
     *
     * @param bool $autodate
     * @param bool $null_values
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function add($autodate = true, $null_values = true)
    {
        if (self::existsInDatabase((int)Configuration::get('LPS_EMPLOYEE_ID'), self::$definition['table'])) {
            return true;
        }

        return parent::add($autodate, $null_values);
    }
}
