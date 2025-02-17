<?php
/**
 * MNG Kargo Entegrasyon Modülü
 *
 * @author    Geliştirici Adı
 * @copyright 2024 MNG Kargo
 * @license   MIT License
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class MNGKargo extends Module
{
    public function __construct()
    {
        $this->name = 'mngkargo';
        $this->tab = 'shipping_logistics';
        $this->version = '1.0.0';
        $this->author = 'BarkodPOS';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('MNG Kargo');
        $this->description = $this->l('PrestaShop için MNG Kargo entegrasyon modülü');
        $this->confirmUninstall = $this->l('Bu modülü kaldırmak istediğinizden emin misiniz?');

        $this->ps_versions_compliancy = array('min' => '1.7.0.0', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        if (!parent::install() ||
            !$this->registerHook('displayAdminOrder') ||
            !$this->registerHook('actionOrderStatusUpdate') ||
            !$this->registerHook('displayOrderDetail') ||
            !$this->installDb() ||
            !$this->installTab() ||
            !Configuration::updateValue('MNGKARGO_LIVE_MODE', false) ||
            !Configuration::updateValue('MNGKARGO_API_KEY', '') ||
            !Configuration::updateValue('MNGKARGO_API_SECRET', '') ||
            !Configuration::updateValue('MNGKARGO_CUSTOMER_NUMBER', '')
        ) {
            return false;
        }
        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall() ||
            !$this->uninstallDb() ||
            !$this->uninstallTab() ||
            !Configuration::deleteByName('MNGKARGO_LIVE_MODE') ||
            !Configuration::deleteByName('MNGKARGO_API_KEY') ||
            !Configuration::deleteByName('MNGKARGO_API_SECRET') ||
            !Configuration::deleteByName('MNGKARGO_CUSTOMER_NUMBER')
        ) {
            return false;
        }
        return true;
    }

    protected function installDb()
    {
        $sql = array();
        
        $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'mngkargo_shipment` (
            `id_shipment` int(11) NOT NULL AUTO_INCREMENT,
            `id_order` int(11) NOT NULL,
            `tracking_number` varchar(50) DEFAULT NULL,
            `shipping_status` varchar(50) DEFAULT NULL,
            `label_url` varchar(255) DEFAULT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`id_shipment`),
            KEY `id_order` (`id_order`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        foreach ($sql as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }
        return true;
    }

    protected function uninstallDb()
    {
        $sql = array();
        $sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'mngkargo_shipment`';

        foreach ($sql as $query) {
            if (!Db::getInstance()->execute($query)) {
                return false;
            }
        }
        return true;
    }

    protected function installTab()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminMNGKargo';
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'MNG Kargo';
        }
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminParentShipping');
        $tab->module = $this->name;
        return $tab->add();
    }

    protected function uninstallTab()
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminMNGKargo');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        }
        return true;
    }

    public function getContent()
    {
        $output = '';

        if (Tools::isSubmit('submit' . $this->name)) {
            $output .= $this->postProcess();
        }

        return $output . $this->renderForm();
    }

    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submit' . $this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Ayarlar'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Canlı Mod'),
                        'name' => 'MNGKARGO_LIVE_MODE',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Evet')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Hayır')
                            )
                        ),
                    ),
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-key"></i>',
                        'desc' => $this->l('MNG Kargo API anahtarınızı girin'),
                        'name' => 'MNGKARGO_API_KEY',
                        'label' => $this->l('API Anahtarı'),
                    ),
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-lock"></i>',
                        'desc' => $this->l('MNG Kargo API şifrenizi girin'),
                        'name' => 'MNGKARGO_API_SECRET',
                        'label' => $this->l('API Şifresi'),
                    ),
                    array(
                        'col' => 4,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-user"></i>',
                        'desc' => $this->l('MNG Kargo müşteri numaranızı girin'),
                        'name' => 'MNGKARGO_CUSTOMER_NUMBER',
                        'label' => $this->l('Müşteri Numarası'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Kaydet'),
                ),
            ),
        );
    }

    protected function getConfigFormValues()
    {
        return array(
            'MNGKARGO_LIVE_MODE' => Configuration::get('MNGKARGO_LIVE_MODE'),
            'MNGKARGO_API_KEY' => Configuration::get('MNGKARGO_API_KEY'),
            'MNGKARGO_API_SECRET' => Configuration::get('MNGKARGO_API_SECRET'),
            'MNGKARGO_CUSTOMER_NUMBER' => Configuration::get('MNGKARGO_CUSTOMER_NUMBER'),
        );
    }

    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }

        return $this->displayConfirmation($this->l('Ayarlar güncellendi'));
    }

    public function hookDisplayAdminOrder($params)
    {
        $order_id = $params['id_order'];
        // Kargo bilgilerini getir ve admin panelinde göster
        $this->context->smarty->assign(array(
            'order_id' => $order_id,
            // Diğer değişkenler...
        ));
        return $this->display(__FILE__, 'views/templates/admin/order_detail.tpl');
    }

    public function hookActionOrderStatusUpdate($params)
    {
        $new_status = $params['newOrderStatus'];
        $order = new Order($params['id_order']);
        
        // Sipariş durumu değiştiğinde gerekli işlemleri yap
    }

    public function hookDisplayOrderDetail($params)
    {
        $order = $params['order'];
        // Müşteri sipariş detay sayfasında kargo bilgilerini göster
        $this->context->smarty->assign(array(
            'tracking_number' => '', // Kargo takip numarası
            'shipping_status' => '', // Kargo durumu
        ));
        return $this->display(__FILE__, 'views/templates/hook/order_detail.tpl');
    }
} 