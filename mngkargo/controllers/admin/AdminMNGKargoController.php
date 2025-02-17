<?php
/**
 * MNG Kargo Admin Controller
 */

class AdminMNGKargoController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'mngkargo_shipment';
        $this->className = 'MNGKargoShipment';
        $this->lang = false;
        $this->addRowAction('view');
        $this->addRowAction('delete');
        
        // Module yüklemesi
        $this->module = Module::getInstanceByName('mngkargo');
        if (!$this->module->active) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules'));
        }

        $this->bulk_actions = array(
            'delete' => array(
                'text' => $this->module->l('Seçilenleri sil'),
                'confirm' => $this->module->l('Seçili gönderileri silmek istediğinizden emin misiniz?'),
                'icon' => 'icon-trash'
            )
        );

        $this->fields_list = array(
            'id_shipment' => array(
                'title' => $this->module->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ),
            'id_order' => array(
                'title' => $this->module->l('Sipariş ID'),
                'align' => 'center',
            ),
            'tracking_number' => array(
                'title' => $this->module->l('Takip No'),
                'align' => 'center',
            ),
            'shipping_status' => array(
                'title' => $this->module->l('Durum'),
                'align' => 'center',
            ),
            'created_at' => array(
                'title' => $this->module->l('Oluşturulma Tarihi'),
                'align' => 'center',
                'type' => 'datetime'
            ),
        );

        parent::__construct();
    }

    public function renderView()
    {
        $id_shipment = Tools::getValue('id_shipment');
        $shipment = new MNGKargoShipment($id_shipment);
        
        // MNG Kargo API'den gönderi detaylarını al
        $api = new MNGKargoAPI(
            Configuration::get('MNGKARGO_API_KEY'),
            Configuration::get('MNGKARGO_API_SECRET'),
            Configuration::get('MNGKARGO_CUSTOMER_NUMBER'),
            Configuration::get('MNGKARGO_LIVE_MODE')
        );

        try {
            $shipmentStatus = $api->getShipmentStatus($shipment->tracking_number);
            $this->context->smarty->assign(array(
                'shipment' => $shipment,
                'shipmentStatus' => $shipmentStatus,
            ));
        } catch (Exception $e) {
            $this->errors[] = $this->l('Gönderi detayları alınamadı: ') . $e->getMessage();
        }

        return $this->createTemplate('shipment_view.tpl')->fetch();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('createShipment')) {
            $id_order = Tools::getValue('id_order');
            $order = new Order($id_order);
            
            if (!Validate::isLoadedObject($order)) {
                $this->errors[] = $this->l('Geçersiz sipariş ID\'si');
                return;
            }

            try {
                $api = new MNGKargoAPI(
                    Configuration::get('MNGKARGO_API_KEY'),
                    Configuration::get('MNGKARGO_API_SECRET'),
                    Configuration::get('MNGKARGO_CUSTOMER_NUMBER'),
                    Configuration::get('MNGKARGO_LIVE_MODE')
                );

                // Sipariş verilerini hazırla
                $orderData = $this->prepareOrderData($order);
                
                // Gönderiyi oluştur
                $response = $api->createShipment($orderData);
                
                if (isset($response['trackingNumber'])) {
                    // Veritabanına kaydet
                    $shipment = new MNGKargoShipment();
                    $shipment->id_order = $id_order;
                    $shipment->tracking_number = $response['trackingNumber'];
                    $shipment->shipping_status = 'created';
                    $shipment->created_at = date('Y-m-d H:i:s');
                    $shipment->updated_at = date('Y-m-d H:i:s');
                    
                    if ($shipment->save()) {
                        $this->confirmations[] = $this->l('Gönderi başarıyla oluşturuldu.');
                    } else {
                        $this->errors[] = $this->l('Gönderi kaydedilemedi.');
                    }
                } else {
                    $this->errors[] = $this->l('Gönderi oluşturulamadı.');
                }
            } catch (Exception $e) {
                $this->errors[] = $this->l('Hata: ') . $e->getMessage();
            }
        }

        parent::postProcess();
    }

    protected function prepareOrderData($order)
    {
        $address = new Address($order->id_address_delivery);
        $customer = new Customer($order->id_customer);
        $state = new State($address->id_state);
        
        return array(
            'reference' => $order->reference,
            'sender' => array(
                'name' => Configuration::get('PS_SHOP_NAME'),
                'address' => Configuration::get('PS_SHOP_ADDR1'),
                'city' => Configuration::get('PS_SHOP_CITY'),
                'district' => Configuration::get('PS_SHOP_DISTRICT', ''),
                'phone' => Configuration::get('PS_SHOP_PHONE'),
            ),
            'receiver' => array(
                'name' => $address->firstname . ' ' . $address->lastname,
                'address' => $address->address1 . ' ' . $address->address2,
                'city' => $state->name,
                'district' => $address->city,
                'phone' => $address->phone,
            ),
            'quantity' => 1, // Varsayılan değer
            'weight' => 1, // Varsayılan değer
            'desi' => 1, // Varsayılan değer
            'paymentType' => 'SENDER', // Varsayılan değer
            'deliveryType' => 'STANDARD', // Varsayılan değer
        );
    }
} 