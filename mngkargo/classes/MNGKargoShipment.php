<?php
/**
 * MNG Kargo Gönderi Sınıfı
 */

class MNGKargoShipment extends ObjectModel
{
    public $id_shipment;
    public $id_order;
    public $tracking_number;
    public $shipping_status;
    public $label_url;
    public $created_at;
    public $updated_at;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'mngkargo_shipment',
        'primary' => 'id_shipment',
        'fields' => array(
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'tracking_number' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 50),
            'shipping_status' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 50),
            'label_url' => array('type' => self::TYPE_STRING, 'validate' => 'isUrl', 'size' => 255),
            'created_at' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'updated_at' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );

    /**
     * Sipariş ID'sine göre gönderi bilgilerini getirir
     */
    public static function getByOrderId($id_order)
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from(self::$definition['table']);
        $query->where('id_order = ' . (int)$id_order);

        $result = Db::getInstance()->getRow($query);
        
        if ($result) {
            $shipment = new MNGKargoShipment();
            $shipment->hydrate($result);
            return $shipment;
        }

        return false;
    }

    /**
     * Takip numarasına göre gönderi bilgilerini getirir
     */
    public static function getByTrackingNumber($tracking_number)
    {
        $query = new DbQuery();
        $query->select('*');
        $query->from(self::$definition['table']);
        $query->where('tracking_number = \'' . pSQL($tracking_number) . '\'');

        $result = Db::getInstance()->getRow($query);
        
        if ($result) {
            $shipment = new MNGKargoShipment();
            $shipment->hydrate($result);
            return $shipment;
        }

        return false;
    }

    /**
     * Gönderi durumunu günceller
     */
    public function updateStatus($status)
    {
        $this->shipping_status = $status;
        $this->updated_at = date('Y-m-d H:i:s');
        return $this->update();
    }

    /**
     * Kargo etiket URL'sini günceller
     */
    public function updateLabelUrl($url)
    {
        $this->label_url = $url;
        $this->updated_at = date('Y-m-d H:i:s');
        return $this->update();
    }
} 