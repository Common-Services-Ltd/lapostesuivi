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
 * Class LaPosteSuiviConfigurationHandler
 */
class LaPosteSuiviConfigurationHandler implements LaPosteSuiviConstantInterface
{
    /** @var LaPosteSuivi */
    protected $module;

    /** @var string */
    protected $local_path;

    /**
     * LaPosteSuiviConfigurationHandler constructor.
     *
     * @param LaPosteSuivi $module
     */
    public function __construct(LaPosteSuivi $module)
    {
        $this->module = $module;
        $this->local_path = _PS_MODULE_DIR_.$this->module->name.'/';
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
     * Create the form that will be displayed in the configuration of your module.
     */
    public function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = 'module';
        $helper->module = $this->module;
        $helper->default_form_language = Context::getContext()->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = 'id_module';
        $helper->submit_action = 'submitLaPosteSuiviModule';
        $helper->currentIndex = Context::getContext()->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->module->name.'&tab_module='.$this->module->tab.'&module_name='.$this->module->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
            'languages' => Context::getContext()->controller->getLanguages(),
            'id_language' => Context::getContext()->language->id
        );

        // Cronjobs
        $shop_url = Tools::getCurrentUrlProtocolPrefix().
            htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__;
        $shop_url = trim($shop_url, '/');
        $token = Configuration::get('LPS_TOKEN');

        Context::getContext()->smarty->assign(array(
            'cron_url' => $shop_url.'/modules/lapostesuivi/functions/tracking.php?token='.$token,
            'cronjobs_url' => $shop_url.'/modules/lapostesuivi/functions/cronjobs.php?token='.$token,
            'cronjobs_is_installed' => Module::isInstalled('cronjobs') &&
                Module::isEnabled('cronjobs'),
        ));

        return $helper->generateForm($this->getConfigForm());
    }

    /**
     * @return array
     */
    public function getConfigFormValues()
    {
        // Carriers
        $carriers = Carrier::getCarriers(Context::getContext()->language->id, true);
        $selected_carriers_references = (array)Tools::unSerialize(Configuration::get('LPS_SELECTED_CARRIERS'));
        $selected_carriers = array();

        foreach ($carriers as $carrier) {
            if (array_search($carrier['id_reference'], $selected_carriers_references) !== false) {
                $selected_carriers[] = $carrier['id_reference'];
            }
        }

        return array(
            'LPS_X_OKAPI_KEY' => Configuration::get('LPS_X_OKAPI_KEY'),
            'LPS_TRACKING_DEADLINE' => Configuration::get('LPS_TRACKING_DEADLINE'),
            'LPS_SELECTED_STATUS' => Configuration::get('LPS_SELECTED_STATUS'),
            'LPS_EMPLOYEE_ID' => Configuration::get('LPS_EMPLOYEE_ID'),
            'LPS_DEBUG' => Configuration::get('LPS_DEBUG'),
            'LPS_SHIPPED_ORDER_STATE' => Configuration::get('LPS_SHIPPED_ORDER_STATE'),
            'LPS_DELIVERED_ORDER_STATE' => Configuration::get('LPS_DELIVERED_ORDER_STATE'),
            'selected_tab' => Tools::getValue('selected_tab', 'nav-authentication'),
            'carriers' => $selected_carriers
        );
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        $fields_form = array();

        // Authentication
        $fields_form[0]['form'] = array(
            'legend' => array(
                'title' => $this->l('Authentication'),
                'icon' => 'icon-unlock-alt'
            ),
            'input' => array(
                array(
                    'type' => 'hidden',
                    'name' => 'selected_tab'
                ),
                array(
                    'type' => 'text',
                    'prefix' => '<i class="icon icon-key"></i>',
                    'name' => 'LPS_X_OKAPI_KEY',
                    'label' => 'X-Okapi-Key',
                    'desc' => array(
                        $this->l('This is your personal security key that allows you to use the La Poste API.'),
                        $this->l('To get your X-Okapi-Key, create an account on La Poste Developer website : '),
                        '<a href="https://developer.laposte.fr/products/suivi/latest" target="_blank">'.
                        'https://developer.laposte.fr/products/suivi/latest</a>',
                        $this->l('Once created, subscribe to the Suivi offer to receive your X-Okapi-Key.')
                    )
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Debug mode'),
                    'name' => 'LPS_DEBUG',
                    'is_bool' => true,
                    'desc' => array(
                        $this->l('Display trace and debug, for development only.'),
                        $this->l('In exploitation this option must not be active !')
                    ),
                    'values' => array(
                        array(
                            'id' => 'active_off',
                            'value' => false,
                            'label' => $this->l('Disabled')
                        ),
                        array(
                            'id' => 'active_on',
                            'value' => true,
                            'label' => $this->l('Enabled')
                        )
                    )
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        // Carriers
        $carrier_options = array();
        foreach (Carrier::getCarriers(Context::getContext()->language->id, true, false, false, null, Carrier::ALL_CARRIERS) as $carrier) {
            $carrier_options[] = array(
                'id_reference' => $carrier['id_reference'],
                'name' => $carrier['name']
            );
        }

        $fields_form[1]['form'] = array(
            'legend' => array(
                'title' => $this->l('Carriers'),
                'icon' => 'icon-truck'
            ),
            'input' => array(
                array(
                    'type' => 'swap',
                    'label' => $this->l('Carriers'),
                    'name' => 'carriers',
                    'options' => array(
                        'query' => $carrier_options,
                        'id' => 'id_reference',
                        'name' => 'name'
                    ),
                    'desc' => array(
                        $this->l('Please select carriers that can be tracked by La Poste.'),
                        $this->l('Only orders made with these selected carriers will be tracked.')
                    )
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        // Status
        $fields_form[2]['form'] = array(
            'legend' => array(
                'title' => $this->l('Status'),
                'icon' => 'icon-time'
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'name' => 'LPS_SHIPPED_ORDER_STATE',
                    'label' => $this->l('Shipped order state'),
                    'options' => $this->getOrderStateOptions(),
                    'desc' => $this->l('Select the order state to set on your orders when they are shipped.')
                ),
                array(
                    'type' => 'select',
                    'name' => 'LPS_DELIVERED_ORDER_STATE',
                    'label' => $this->l('Delivered order state'),
                    'options' => $this->getOrderStateOptions(),
                    'desc' => $this->l('Select the order state to set on your orders when they are delivered.')
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        // Settings
        $fields_form[3]['form'] = array(
            'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cog'
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'name' => 'LPS_TRACKING_DEADLINE',
                    'label' => $this->l('Track orders until'),
                    'options' => $this->getDeadlineOptions(),
                    'desc' => array(
                        $this->l('Select until how old an order needs to be in order to be tracked.'),
                        $this->l('Usually, 1 or 2 weeks are enough as packages are delivered within a few days.'),
                        $this->l('If there is a delay between the order creation and the shipment on your shop').' '.
                            $this->l('then select a higher value.')
                    )
                ),
                array(
                    'type' => 'select',
                    'name' => 'LPS_EMPLOYEE_ID',
                    'label' => $this->l('Cron task employee'),
                    'options' => $this->getEmployeeOptions(),
                    'desc' => $this->l('Select the employee that will be used by the module to change the order state.')
                ),
                array(
                    'type' => 'html',
                    'label' => $this->l('Cron task'),
                    'name' => null,
                    'html_content' => Context::getContext()->smarty->fetch(
                        $this->local_path.'views/templates/admin/helpers/cron.tpl'
                    )
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );

        return $fields_form;
    }

    /**
     * @return array
     */
    protected function getEmployeeOptions()
    {
        $employees = array();
        foreach (Employee::getEmployees() as $employee) {
            $employees[] = array(
                'id_employee' => (int)$employee['id_employee'],
                'name' => $employee['firstname'].' '.$employee['lastname']
            );
        }

        $options = array(
            'query' => $employees,
            'id' => 'id_employee',
            'name' => 'name'
        );

        try {
            $module_employee = new Employee(Configuration::get('LPS_EMPLOYEE_ID'));
        } catch (Exception $exception) {
            Tools::dieOrLog($exception->getMessage(), false);
        }

        if (Validate::isLoadedObject($module_employee)) {
            $options['default'] = array(
                'value' => (int)$module_employee->id,
                'label' => $module_employee->firstname.' '.$module_employee->lastname
            );

            foreach ($options['query'] as $key => $option) {
                if ($option['id_employee'] == $module_employee->id) {
                    unset($options['query'][$key]);
                    break;
                }
            }
        }

        return $options;
    }

    /**
     * @return array
     */
    protected function getOrderStateOptions()
    {
        $options = array();

        foreach (LaPosteSuiviOrderState::getOrderStatesShipped(Context::getContext()->language->id) as $order_state) {
            $options[] = array(
                'value' => $order_state['id_order_state'],
                'name' => $order_state['name']
            );
        }

        return array(
            'query' => $options,
            'id' => 'value',
            'name' => 'name'
        );
    }

    /**
     * @return array
     */
    protected function getDeadlineOptions()
    {
        $weeks_lang = $this->l('weeks');
        $tracking_deadline_options = array(
            '-',
            array(
                'value' => 7,
                'name' => '1 '.$this->l('week')
            ),
            array(
                'value' => 21,
                'name' => '3 '.$weeks_lang
            ),
            array(
                'value' => 28,
                'name' => '4 '.$weeks_lang
            ),
            array(
                'value' => 35,
                'name' => '5 '.$weeks_lang
            ),
            array(
                'value' => 42,
                'name' => '6 '.$weeks_lang
            ),
            array(
                'value' => 49,
                'name' => '7 '.$weeks_lang
            ),
            array(
                'value' => 56,
                'name' => '8 '.$weeks_lang
            ),
            array(
                'value' => 63,
                'name' => '9 '.$weeks_lang
            ),
            array(
                'value' => 70,
                'name' => '10 '.$weeks_lang
            ),
            array(
                'value' => 77,
                'name' => '11 '.$weeks_lang
            ),
            array(
                'value' => 84,
                'name' => '12 '.$weeks_lang
            )
        );

        return array(
            'query' => $tracking_deadline_options,
            'id' => 'value',
            'name' => 'name',
            'default' => array(
                'value' => '14',
                'label' => '2 '.$weeks_lang
            )
        );
    }
}
