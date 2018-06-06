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
 * Class LaPosteSuiviTab
 */
class LaPosteSuiviTab extends Tab
{

    /**
     * Create the tab and return its ID, or false on error.
     *
     * @return bool|int
     */
    public static function create()
    {
        $id_tab = Tab::getIdFromClassName(LaPosteSuivi::TAB_CLASS_NAME);
        if ($id_tab) {
            return $id_tab;
        }

        try {
            $tab = new self();
            $tab_name = array();

            foreach (Language::getLanguages() as $language) {
                $tab_name[$language['id_lang']] = 'La Poste Suivi';
            }

            $tab->name = $tab_name;
            $tab->class_name = LaPosteSuivi::TAB_CLASS_NAME;
            $tab->module = 'lapostesuivi';
            $tab->id_parent = Tab::getIdFromClassName(
                version_compare(_PS_VERSION_, '1.7', '>=') ?
                    'AdminParentOrders' : 'AdminOrders'
            );
            $tab->add();
        } catch (Exception $exception) {
            Tools::dieOrLog($exception->getMessage(), false);
            return false;
        }

        return $tab->id;
    }

    /**
     * @return bool
     */
    public static function remove()
    {
        $id_tab = Tab::getIdFromClassName(LaPosteSuivi::TAB_CLASS_NAME);
        if (!$id_tab) {
            return true;
        }

        try {
            $tab = new self($id_tab);
            if (Validate::isLoadedObject($tab)) {
                return $tab->delete();
            }
        } catch (Exception $exception) {
            Tools::dieOrLog($exception->getMessage(), false);
        }

        return false;
    }

    /**
     * Create only 1 LaPoste Suivi tab.
     *
     * @param bool $autodate
     * @param bool $null_values
     * @return bool|int
     */
    public function add($autodate = true, $null_values = false)
    {
        if (Tab::getIdFromClassName('AdminOrdersLaPosteSuivi')) {
            return true;
        }

        return parent::add($autodate, $null_values);
    }
}
