<?php
/**
 * @author    debuss-a <alexandre@common-services.com>
 * @copyright Copyright (c) 2018 Common-Services
 * @license   CC BY-SA 4.0
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__).'/bootstrap/autoload.php';

/**
 * Class LaPosteSuivi
 */
class LaPosteSuivi extends Module implements LaPosteSuiviConstantInterface
{

    const TAB_CLASS_NAME = 'AdminOrdersLaPosteSuivi';

    /** @var string */
    public $folder_path;

    /** @var array */
    public $status_code = array(
        'PRIS_EN_CHARGE' => null,
        'EN_LIVRAISON' => null,
        'EXPEDIE' => null,
        'A_RETIRER' => null,
        'LIVRE' => null,
        'TRI_EFFECTUE' => null,
        'DISTRIBUE' => null,
        'DESTINATAIRE_INFORME' => null,
        'RETOUR_DESTINATAIRE' => null,
        'ERREUR' => null,
        'INCONNU' => null,
    );

    /** @var LaPosteSuiviHooksHandler */
    protected $hooks_handler;

    /** @var LaPosteSuiviConfigurationHandler */
    protected $config_handler;

    /**
     * LaPosteSuivi constructor.
     */
    public function __construct()
    {
        $this->name = 'lapostesuivi';
        $this->tab = 'shipping_logistics';
        $this->version = '1.0.04';
        $this->author = 'debuss-a';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = 'La Poste Suivi';
        $this->description = $this->l('Track your package from La Poste, Colissimo, Chronopost.');

        $this->folder_path = $this->local_path;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

        $this->hooks_handler = new LaPosteSuiviHooksHandler($this);
        $this->config_handler = new LaPosteSuiviConfigurationHandler($this);

        if (Tools::getValue('LPS_DEBUG', Configuration::get('LPS_DEBUG')) && !_PS_MODE_DEV_) {
            @ini_set('display_errors', 'on');
            @error_reporting(E_ALL|E_STRICT);
        }

        $this->setStatusCodeTranslation();
    }

    protected function setStatusCodeTranslation()
    {
        $this->status_code['PRIS_EN_CHARGE'] = $this->l('Package is dealt with, or is processing.');
        $this->status_code['EN_LIVRAISON'] = $this->l('The package is being delivered, or ready to be sent.');
        $this->status_code['EXPEDIE'] = $this->l('The package has been sent.');
        $this->status_code['A_RETIRER'] = $this->l('The package is available, can be withdrawn from relay.');
        $this->status_code['TRI_EFFECTUE'] = $this->l('The package has been sorted.');
        $this->status_code['DISTRIBUE'] = $this->l('The package has been distributed.');
        $this->status_code['LIVRE'] = $this->l('The package has been delivered.');
        $this->status_code['DESTINATAIRE_INFORME'] = $this->l('The recipient has been informed.');
        $this->status_code['RETOUR_DESTINATAIRE'] = $this->l('The package has been returned.');
        $this->status_code['ERREUR'] = $this->l('The package delivery has an error.');
        $this->status_code['INCONNU'] = $this->l('The status is unknown (an error is possible).');
    }

    /**
     * @return bool
     */
    public function install()
    {
        require_once dirname(__FILE__).'/sql/install.php';

        return parent::install() &&
            $this->hooks_handler->setHooks(self::ADD) &&
            LaPosteSuiviEmployee::create() &&
            LaPosteSuiviTab::create() &&
            Configuration::updateValue('LPS_TRACKING_DEADLINE', '14') &&
            Configuration::updateValue('LPS_DEBUG', 0) &&
            Configuration::updateValue('LPS_TOKEN', md5(LaPosteSuiviTools::generateRandomPassword()));
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        require_once dirname(__FILE__).'/sql/uninstall.php';

        return parent::uninstall() &&
            $this->hooks_handler->setHooks(self::REMOVE) &&
            LaPosteSuiviEmployee::remove() &&
            LaPosteSuiviTab::remove() &&
            Configuration::deleteByName('LPS_X_OKAPI_KEY') &&
            Configuration::deleteByName('LPS_TOKEN') &&
            Configuration::deleteByName('LPS_SELECTED_CARRIERS') &&
            Configuration::deleteByName('LPS_TRACKING_DEADLINE') &&
            Configuration::deleteByName('LPS_SELECTED_STATUS') &&
            Configuration::deleteByName('LPS_EMPLOYEE_ID') &&
            Configuration::deleteByName('LPS_DEBUG');
    }

    /**
     * @param string|array $errors
     */
    public function addErrors($errors)
    {
        foreach ((array)$errors as $error) {
            $this->_errors[] = $error;
        }
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submitLaPosteSuiviModule')) {
            $output .= $this->postProcess();
        }

        $this->context->smarty->assign(array(
            'ps_version' => _PS_VERSION_,
            'module_dir' => $this->_path,
            'module_version' => $this->version
        ));

        $this->context->controller->addCSS($this->_path.'/views/css/back.css');
        $this->context->controller->addJS($this->_path.'/views/js/back.js');

        $output .= $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->config_handler->renderForm();
    }

    /**
     * Save form data.
     *
     * @return string
     */
    protected function postProcess()
    {
        require_once dirname(__FILE__).'/sql/install.php';

        $this->hooks_handler->setHooks(self::UPDATE);
        LaPosteSuiviEmployee::create();
        LaPosteSuiviTab::create();

        $success = true;
        $success &= Configuration::updateValue('LPS_X_OKAPI_KEY', Tools::getValue('LPS_X_OKAPI_KEY'));
        $success &= Configuration::updateValue(
            'LPS_SELECTED_CARRIERS',
            serialize(Tools::getValue('carriers_selected'))
        );
        $success &= Configuration::updateValue('LPS_TRACKING_DEADLINE', Tools::getValue('LPS_TRACKING_DEADLINE'));
        $success &= Configuration::updateValue('LPS_SELECTED_STATUS', serialize(Tools::getValue('selected_status')));
        $success &= Configuration::updateValue('LPS_EMPLOYEE_ID', Tools::getValue('LPS_EMPLOYEE_ID'));
        $success &= Configuration::updateValue('LPS_DEBUG', Tools::getValue('LPS_DEBUG'));

        if ($success) {
            return $this->displayConfirmation($this->l('Settings updated'));
        }

        return $this->displayError($this->l('Invalid Configuration value')).
            $this->displayError($this->_errors);
    }

    /**
     * @param $params
     * @return string
     * @throws Exception
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookDisplayAdminOrder($params)
    {
        return $this->hooks_handler->displayAdminOrder($params);
    }

    /**
     * @param $params
     * @return bool
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookDisplayOrderDetail($params)
    {
        return $this->hooks_handler->displayOrderDetail($params);
    }

    /**
     * Add a column "Tracking number" on the Order List page.
     *
     * @param $params
     */
    public function hookActionAdminOrdersListingFieldsModifier($params)
    {
        $this->hooks_handler->actionAdminOrdersListingFieldsModifier($params);
    }
}
