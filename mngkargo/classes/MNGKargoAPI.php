<?php
/**
 * MNG Kargo API Entegrasyon Sınıfı
 */

class MNGKargoAPI
{
    private $apiKey;
    private $apiSecret;
    private $customerNumber;
    private $isLiveMode;
    private $apiUrl;

    public function __construct($apiKey, $apiSecret, $customerNumber, $isLiveMode = false)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->customerNumber = $customerNumber;
        $this->isLiveMode = $isLiveMode;
        $this->apiUrl = $isLiveMode 
            ? 'https://api.mngkargo.com.tr/api/v1' 
            : 'https://test-api.mngkargo.com.tr/api/v1';
    }

    /**
     * API isteği için kimlik doğrulama başlıklarını oluşturur
     */
    private function getAuthHeaders()
    {
        $timestamp = time();
        $hash = hash_hmac('sha256', $this->apiKey . $timestamp, $this->apiSecret);

        return array(
            'X-API-Key: ' . $this->apiKey,
            'X-Timestamp: ' . $timestamp,
            'X-Signature: ' . $hash,
            'Content-Type: application/json'
        );
    }

    /**
     * API isteği gönderir
     */
    private function makeRequest($endpoint, $method = 'GET', $data = null)
    {
        $curl = curl_init();

        $options = array(
            CURLOPT_URL => $this->apiUrl . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $this->getAuthHeaders(),
        );

        if ($data !== null) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            throw new Exception('API İsteği Başarısız: ' . $err);
        }

        return json_decode($response, true);
    }

    /**
     * Yeni gönderi oluşturur
     */
    public function createShipment($orderData)
    {
        $endpoint = '/shipments';
        $data = array(
            'customerNumber' => $this->customerNumber,
            'referenceNo' => $orderData['reference'],
            'sender' => array(
                'name' => $orderData['sender']['name'],
                'address' => $orderData['sender']['address'],
                'city' => $orderData['sender']['city'],
                'district' => $orderData['sender']['district'],
                'phone' => $orderData['sender']['phone'],
            ),
            'receiver' => array(
                'name' => $orderData['receiver']['name'],
                'address' => $orderData['receiver']['address'],
                'city' => $orderData['receiver']['city'],
                'district' => $orderData['receiver']['district'],
                'phone' => $orderData['receiver']['phone'],
            ),
            'pieces' => array(
                array(
                    'quantity' => $orderData['quantity'],
                    'weight' => $orderData['weight'],
                    'desi' => $orderData['desi'],
                )
            ),
            'paymentType' => $orderData['paymentType'],
            'deliveryType' => $orderData['deliveryType'],
        );

        return $this->makeRequest($endpoint, 'POST', $data);
    }

    /**
     * Gönderi durumunu sorgular
     */
    public function getShipmentStatus($trackingNumber)
    {
        $endpoint = '/shipments/' . $trackingNumber . '/status';
        return $this->makeRequest($endpoint);
    }

    /**
     * Kargo etiketini oluşturur
     */
    public function createShippingLabel($trackingNumber)
    {
        $endpoint = '/shipments/' . $trackingNumber . '/label';
        return $this->makeRequest($endpoint);
    }

    /**
     * Gönderiyi iptal eder
     */
    public function cancelShipment($trackingNumber)
    {
        $endpoint = '/shipments/' . $trackingNumber . '/cancel';
        return $this->makeRequest($endpoint, 'POST');
    }
} 