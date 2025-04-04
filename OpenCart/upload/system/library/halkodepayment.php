<?php

class halkodepayment
{
    public $debug = false;
    public $env = 'test';
    public $env_domain = array(
        'test' => 'https://testapp.halkode.com.tr',
        'prod' => 'https://app.halkode.com.tr'
    );
    public $items;
    public $customer;
    public $card;
    private $credentials;
    public $is_3d;
    public $order;
    private $app_id;
    private $app_secret;
    private $merchant_key;
    public $token;
    public $webhookurl;
    public $sale_web_hook_key;
    public $recurring_web_hook_key;
    public $response;
    public $return_url;
    public $cancel_url;
    public $paymentid;
    public $billing;
    public $shipping;

    public function __construct($app_id, $app_secret, $merchant_key, $sale_web_hook_key, $recurring_web_hook_key, $env = 'test', $debug = false)
    {
        $this->debug = $debug;
        $this->app_id = $app_id;
        $this->app_secret = $app_secret;
        $this->merchant_key = $merchant_key;
        $this->sale_web_hook_key = $sale_web_hook_key;
        $this->recurring_web_hook_key = $recurring_web_hook_key;
        $this->env = $env;
    }

    /**
     * @param $total
     * @param $installment
     * @param $currency_code
     * @param $merchant_key
     * @param $invoice_id
     * @param $app_secret
     * @return string
     */
    private function generateHashKey($parts, $app_secret): string
    {

        //$data = $total . '|' . $installment . '|' . $currency_code . '|' . $merchant_key . '|' . $invoice_id;
        $data = implode("|", $parts);

        $iv = substr(sha1(mt_rand()), 0, 16);
        $password = sha1($app_secret);

        $salt = substr(sha1(mt_rand()), 0, 4);
        $saltWithPassword = hash('sha256', $password . $salt);

        $encrypted = openssl_encrypt(
            "$data", 'aes-256-cbc', "$saltWithPassword", 0, $iv
        );
        $msg_encrypted_bundle = "$iv:$salt:$encrypted";
        $msg_encrypted_bundle = str_replace('/', '__', $msg_encrypted_bundle);
        return $msg_encrypted_bundle;
    }

    /**
     * @param $endpoint
     * @return string
     */
    public function getUrl($endpoint): string
    {
        return $this->env_domain[$this->env] . $endpoint;
    }

    public function getHeader()
    {

        $return = array('Accept: application/json', 'Content-Type: application/json');

        if (isset($this->token) and $this->token != "")
            $return[] = 'Authorization: ' . $this->token;

        return $return;
    }

    public function checkStatus($invoice_id)
    {
        if ($this->token == "") {
            $status = $this->getToken();
            if ($status["status"] != "success")
                die($status["message"]);
        }
 
        $requestParams = [
            'invoice_id' => $invoice_id,
            'merchant_key' => $this->merchant_key,
            'hash_key' => $this->generateHashKey([$invoice_id,$this->merchant_key] , $this->app_secret),

        ];

        $this->response = $this->makeRequest("/ccpayment/api/checkstatus", $requestParams);
        
        if (isset($this->response->status_code) && $this->response->status_code == 100) {
            return [
                "status" => "success",
                "message" => "",
                "data" => $this->response->data
            ];
        } else {
            return [
                "status" => "error",
                "message" => $this->response->status_description,
            ];
        }

    }

    public function getToken()
    {
        $requestParams = [
            'app_id' => $this->app_id,
            'app_secret' => $this->app_secret
        ];

        $this->response = $this->makeRequest("/ccpayment/api/token", $requestParams);
        //dump($this->response);exit;
        if (isset($this->response->status_code) && $this->response->status_code == 100) {
            $this->token = 'Bearer ' . $this->response->data->token;
            $this->is_3d = $this->response->data->is_3d;

            return [
                "status" => "success",
                "message" => "",
            ];
        } else {
            return [
                "status" => "error",
                "message" => $this->response->status_description,
            ];
        }
    }

    public function makeRequest($endpoint, $data, $method = 'POST')
    {

        $url = $this->getUrl($endpoint);

        if ($method == 'GET')
            $url .= "?" . http_build_query($data);

        $ch = curl_init($url);

        if ($method == 'POST')
            curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeader());
        if ($method == 'POST')
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        if( $this->env == 'test')
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90);
        $output1 = curl_exec($ch);
        $output2 = json_decode($output1);
        curl_close($ch);

        if ($this->debug) {
            dump([
                "endpoint" => $endpoint,
                "header" => $this->getHeader(),
                "data" => $data,
                "output1" => $output1,
                "output2" => $output2,
            ]);
        }

        if (!isJson($output1))
            return false;

        return $output2;
    }

    public function getInstallments($amount, $curr, $cc_no)
    {
        if ($this->token == "") {
            $status = $this->getToken();
            if ($status["status"] != "success")
                die($status["message"]);
        }

        $requestParams = [
            'credit_card' => $cc_no,
            'amount' => $amount,
            'currency_code' => $curr,
            'merchant_key' => $this->merchant_key,
            'is_recurring' => "",
            'is_2d' => $this->is_3d == "0" ? 1 : "",
        ];

        $this->response = $this->makeRequest("/ccpayment/api/getpos", $requestParams);

        if (isset($this->response->status_code) && $this->response->status_code == 100) {
            return [
                "status" => "success",
                "message" => "",
                "data" => $this->response->data
            ];
        } else {
            return [
                "status" => "error",
                "message" => $this->response->status_description,
            ];
        }

    }

    public function getStoreInstallments(){
        ///api/installments
        if ($this->token == "") {
            $status = $this->getToken();
            if ($status["status"] != "success")
                return [
                    "status" => "error",
                    "message" => $this->response->status_description,
                ];
        }

        $requestParams = [
            'merchant_key' => $this->merchant_key,
        ];

        $this->response = $this->makeRequest("/ccpayment/api/installments", $requestParams);


        if (isset($this->response->status_code) && $this->response->status_code == 100) {
            return [
                "status" => "success",
                "message" => "",
                "data" => $this->response->installments
            ];
        } else {
            return [
                "status" => "error",
                "message" => ( isset($this->response->status_description) ? $this->response->status_description : $this->response->message ),
            ];
        }


    }

    public function generateSavedCardForm( $savedCardToken )
    {
        $invoice = $this->_generateFormFields( $savedCardToken );
        return [
            "status" => "success",
            "form" =>
                [
                    "url" => $this->getUrl("/ccpayment/api/payByCardToken"),
                    "inputs" => $invoice,
                ]
        ];
    }

    public function generate3DForm(  )
    {
        $invoice = $this->_generateFormFields(  );
        //dump($invoice);exit;

        return [
            "status" => "success",
            "form" =>
                [
                    "url" => $this->getUrl("/ccpayment/api/paySmart3D"),
                    "inputs" => $invoice,
                ]
        ];
    }

    public function generate2DForm(  ){
        // API ile ödeme yapmaya çalış dönen mesajı direk validation a form olarak gönder
        $fields = $this->_generateFormFields( false, false );
    
        if ($this->token == "") {
            $status = $this->getToken();
            if ($status["status"] != "success")
                die($status["message"]);
        }

        $this->response = $this->makeRequest("/ccpayment/api/paySmart2D", $fields);
        
        if (isset($this->response->status_code) && $this->response->status_code == 100) {
            $returnForm = (array) $this->response + (array) $this->response->data;
            unset($returnForm['data']);
            //dump($returnForm);exit;

            $redirecti = $this->return_url;
            if( strstr($this->return_url, '?') )
                $redirecti .= '&';
            else
                $redirecti .= '?';
            $redirecti .= http_build_query($returnForm);

            return [
                "status" => "success",
                "message" => "",
                "data" => $this->response->data,
                "redirect" => $redirecti,
                "form2" => [
                        "url" => $this->return_url,
                        "inputs" => $returnForm,
                    ],
            ];
        } else {
            return [
                "status" => "error",
                "message" => $this->response->status_description,
            ];
        }
    }

    

    private function _generateFormFields( $useCard = false , $encodeItems = true ){
        if ($this->token == "") {
            $status = $this->getToken();
            if ($status["status"] != "success")
                die($status["message"]);
        }

        $items = json_encode($this->items);
        if ($this->debug) {
            echo "<hr>";
            var_dump($items);
        }
        $items = urlencode($items);

        if ($this->debug) {
            echo "<hr>";
            var_dump($items);
        }


        $invoice_id = $this->paymentid;
        $currency_code = $this->order["currency"]; //Merchant currency code e.g(TRY,USD,EUR)

        $total = $this->order["total"];
        $total = 0;
        foreach ($this->items as $item) {
            $cartItem = [
                'name' => $item["product_name"],
                'price' => $item["product_price"],
                'quantity' => intval( $item["product_quantity"] ),
                'description' => "",
            ];

            //$item["recurring"]

            $cart[] = $cartItem;
            $productPrice = $cartItem['price'] * $cartItem['quantity'];
            $total = $total + $productPrice;
            if( isset($item["recurring"]) )
                $recurring = $item["recurring"];
        }

        // @TODO find total via installment commision
        if($this->order["total"] != $this->order["sub_total"]){
            $productPrice = number_format(abs($this->order["total"] - $this->order["sub_total"]),2, '.','');
            $cartItem = [
                'name' => "Taksit Komisyonu",
                'price' => $productPrice,
                'quantity' => 1,
                'description' => "",
            ];
            $cart[] = $cartItem;
            $total = $total + $productPrice;
        }

        $item_js = $cart;
        if( $encodeItems )
            $item_js = json_encode($cart);

        //$item_js = str_replace("\"", "'", $item_js);
        $name = $this->customer["firstname"];
        $surname = $this->customer["lastname"];
        $installment = $this->order["installment"];

        if( isset($this->order['discount']) AND $this->order['discount'] > 0 ){
            $total -= $this->order['discount'];
        }

        $hash_key = $this->generateHashKey(
            [
                $total,
                $installment,
                $currency_code,
                $this->merchant_key,
                $invoice_id,
            ],
            $this->app_secret
        );

        $invoice = [
            'merchant_key' => $this->merchant_key,
            'invoice_id' => $invoice_id,
            'total' => $total,
            'items' => $item_js,
            'currency_code' => $currency_code,
            'installments_number' => $installment,
            'cancel_url' => $this->cancel_url,
            'return_url' => $this->return_url,
            'hash_key' => $hash_key,
            'name' => $name,
            'surname' => $surname,
        ];

        if( !$useCard ){
            $cardParams = [
                'cc_holder_name' => $this->card["owner"],
                'cc_no' => $this->card["pan"],
                'expiry_month' => $this->card["month"],
                'expiry_year' => $this->card["year"],
                'cvv' => $this->card["cvc"],
            ];
            $invoice = array_merge($invoice, $cardParams);
        }else{
            $cardParams = [
                "card_token" => $useCard,
                "customer_number" => $this->customer["id"],
                "customer_email" => $this->customer["email"],
                "customer_phone" => $this->customer["phone"],
                "customer_name" => $this->customer["name"],
            ];
            $invoice = array_merge($invoice, $cardParams);
        }

        //billing info
        $invoice['bill_address1'] = $this->billing["address1"]; //should not more than 100 characters
        $invoice['bill_address2'] = $this->billing["address2"]; //should not more than 100 characters
        $invoice['bill_city'] = $this->billing["city"];
        $invoice['bill_postcode'] = $this->billing["postcode"];
        $invoice['bill_state'] = $this->billing["state"];
        $invoice['bill_country'] = $this->billing["country"];
        $invoice['bill_phone'] = $this->billing["phone"];
        $invoice['bill_email'] = $this->billing["email"];

        if( isset($this->order["discount"]) AND $this->order["discount"] > 0 ){
            $invoice['discount'] = $this->order["discount"];
            $invoice['coupon'] = 'COUPON';
        }

        if (isset($this->order["transaction_type"]) and $this->order["transaction_type"] != "")
            $invoice["transaction_type"] = $this->order["transaction_type"];

        if (isset($this->order["recurring"]) and $this->order["recurring"] == 1) {
            $invoice["order_type"] = "1";
            $invoice["recurring_payment_number"] = $this->order["recurring_payment_number"];
            $invoice["recurring_payment_cycle"] = $this->order["recurring_payment_cycle"];
            $invoice["recurring_payment_interval"] = $this->order["recurring_payment_interval"];
            $invoice["recurring_web_hook_key"] = $this->recurring_web_hook_key;
        } else {
            $invoice['sale_web_hook_key'] = $this->sale_web_hook_key;
        }
        return $invoice;
    }

    public function generatePaymentLink()
    {
        if ($this->token == "") {
            $status = $this->getToken();
            if ($status["status"] != "success")
                die($status["message"]);
        }

        
        $items = json_encode($this->items);
        if ($this->debug) {
            echo "<hr>";
            var_dump($items);
        }
        $items = urlencode($items);


        if ($this->debug) {
            echo "<hr>";
            var_dump($items);
        }


        $invoice_id = $this->paymentid;
        $currency_code = $this->order["currency"]; //Merchant currency code e.g(TRY,USD,EUR)

        $total = 0;
        foreach ($this->items as $item) {
            $cartItem = [
                'name' => $item["product_name"],
                'price' => $item["product_price"],
                'quantity' => $item["product_quantity"],
                'description' => "",
            ];
            $cart[] = $cartItem;
            $productPrice = $cartItem['price'] * $cartItem['quantity'];
            $total = $total + $productPrice;
            if(isset($item["recurring"]))
                $recurring = $item["recurring"];
        }

        $item_js = ($cart);
        $name = $this->customer["firstname"];
        $surname = $this->customer["lastname"];
        $sale_web_hook = $this->order["key"];//put your web hook
        $installment = $this->order["installment"];

        if( isset($this->order['discount']) AND $this->order['discount'] > 0 ){
            $total -= $this->order['discount'];
        }

        $hash_key = $this->generateHashKey([
            $total,
            $installment,
            $currency_code,
            $this->merchant_key,
            $invoice_id,
        ],

            $this->app_secret
        );

        $invoice = [
            'invoice_description' => 'asd',
            'invoice_id' => $invoice_id,
            'total' => $total,
            'items' => ($item_js),
            
            'max_installment' => $installment,
            'cancel_url' => $this->cancel_url,
            'return_url' => $this->return_url,
            //'hash_key' => $hash_key,
            
        ];

        //billing info
        $invoice['bill_address1'] = $this->billing["address1"]; //should not more than 100 characters
        $invoice['bill_address2'] = $this->billing["address2"]; //should not more than 100 characters
        $invoice['bill_city'] = $this->billing["city"];
        $invoice['bill_postcode'] = $this->billing["postcode"];
        $invoice['bill_state'] = $this->billing["state"];
        $invoice['bill_country'] = $this->billing["country"];
        $invoice['bill_phone'] = $this->billing["phone"];
        $invoice['bill_email'] = $this->billing["email"];

        if( isset($this->order["discount"]) AND $this->order["discount"] > 0 ){
            $invoice['discount'] = $this->order["discount"];
            $invoice['coupon'] = 'COUPON';
        }

        if (isset($this->order["transaction_type"]) and $this->order["transaction_type"] != "")
            $invoice["transaction_type"] = $this->order["transaction_type"];

        if (isset($this->order["recurring"]) and $this->order["recurring"] == 1) {
            $invoice["order_type"] = "1";
            $invoice["recurring_payment_number"] = $this->order["recurring_payment_number"];
            $invoice["recurring_payment_cycle"] = $this->order["recurring_payment_cycle"];
            $invoice["recurring_payment_interval"] = $this->order["recurring_payment_interval"];
            $invoice["recurring_web_hook_key"] = $this->recurring_web_hook_key;
        } else {
            $invoice['sale_web_hook_key'] = $this->sale_web_hook_key;
        }


        $postdata = [
                    'merchant_key' => $this->merchant_key,
                    'invoice' => json_encode($invoice),
                    'currency_code' =>  $currency_code,
                    'name' => $name,
                    'surname' => $surname,
                ];

        $this->response = $this->makeRequest("/ccpayment/purchase/link", $postdata);
        
        if (isset($this->response->status_code) && $this->response->status_code == 100) {
            return [
                "status" => "success",
                "redirect" => $this->response->link,
                "inputs" => $invoice,
            ];
        } else {
            return [
                "status" => "error",
                "message" => isset($this->response->success_message) ?  $this->response->success_message :  $this->response->status_description,
            ];
        }
    }

    public function getStoredCards($customerNumber)
    {
        if ($this->token == "") {
            $status = $this->getToken();
            if ($status["status"] != "success")
                die($status["message"]);
        }

        $requestParams = [
            'merchant_key' => $this->merchant_key,
            'customer_number' => $customerNumber,
        ];

        if (!$this->response = $this->makeRequest("/ccpayment/api/getCardTokens", $requestParams, 'GET'))
            return [
                "status" => "error",
                "message" => $this->response->status_description,
                "data" => [],
                "cards" => []
            ];

        $cards = [];
        if (isset($this->response->status_code) and $this->response->status_code == 100) {

            foreach ($this->response->data as $customerCard) {
                $cards[$customerCard->card_token] = $customerCard;
            }

            return [
                "status" => "success",
                "message" => "",
                "data"  => $this->response->data,
                "cards" => $cards
            ];
        } else {
            return [
                "status" => "error",
                "message" => $this->response->status_description,
            ];
        }
    }

    public function updateStoredCard($card_token, $customer_number, $expiry_month, $expiry_year, $card_holder_name)
    {
        if ($this->token == "") {
            $status = $this->getToken();
            if ($status["status"] != "success")
                die($status["message"]);
        }

        $requestParams = [
            'merchant_key' => $this->merchant_key,
            'card_token' => $card_token,
            'customer_number' => $customer_number,
            'expiry_month' => $expiry_month,
            'expiry_year' => $expiry_year,
            'hash_key' => $this->generateHashKey([$this->merchant_key, $customer_number, $card_token], $this->app_secret),
            'card_holder_name' => $card_holder_name,
        ];

        if (!$this->response = $this->makeRequest("/ccpayment/api/editCard", $requestParams, 'POST'))
            return [
                "status" => "error",
                "message" => $this->response->status_description,
                "data" => [],
                "cards" => []
            ];

        $cards = [];
        if (isset($this->response->status_code) and $this->response->status_code == 100) {
            return [
                "status" => "success",
                "message" => "",
            ];
        } else {
            return [
                "status" => "error",
                "message" => $this->response->status_description,
            ];
        }
    }

    public function deleteStoredCard($card_token, $customer_number)
    {
        if ($this->token == "") {
            $status = $this->getToken();
            if ($status["status"] != "success")
                die($status["message"]);
        }

        $requestParams = [
            'merchant_key' => $this->merchant_key,
            'card_token' => $card_token,
            'customer_number' => $customer_number,
            'hash_key' => $this->generateHashKey([$this->merchant_key, $customer_number, $card_token], $this->app_secret),
        ];

        if (!$this->response = $this->makeRequest("/ccpayment/api/deleteCard", $requestParams, 'POST'))
            return [
                "status" => "error",
                "message" => $this->response->status_description,
                "data" => [],
                "cards" => []
            ];

        $cards = [];
        if (isset($this->response->status_code) and $this->response->status_code == 100) {
            
            foreach ($this->response->data as $customerCard) {
                $cards[$customerCard->card_token] = $customerCard;
            }

            return [
                "status" => "success",
                "message" => "",
                "data"  => $this->response->data,
                "cards" => $cards
            ];
        } else {
            return [
                "status" => "error",
                "message" => $this->response->status_description,
            ];
        }
    }

    public function storeCard($customer_number, $card_holder, $card_number, $expiry_month, $expiry_year, $customer_name, $customer_phone)
    {
        if ($this->token == "") {
            $status = $this->getToken();
            if ($status["status"] != "success")
                die($status["message"]);
        }

        $requestParams = [
            'merchant_key' => $this->merchant_key,
            "card_holder_name" => $card_holder,
            "card_number" => $card_number,
            "expiry_month" => $expiry_month,
            "expiry_year" => $expiry_year,
            "customer_number" => $customer_number,
            "hash_key" => $this->generateHashKey([$this->merchant_key, $customer_number, $card_holder, $card_number, $expiry_month, $expiry_year], $this->app_secret),
            "customer_name" => $customer_name,
            "customer_phone" => $customer_phone,
        ];

        if (!$this->response = $this->makeRequest("/ccpayment/api/saveCard", $requestParams))
            return [
                "status" => "error",
                "message" => "Sistemsel bir hata oluştu",
            ];;

        if (isset($this->response->status_code) and $this->response->status_code == 100) {
            return [
                "status" => "success",
                "message" => "",
                "data" => $this->response->card_token
            ];
        } else {
            return [
                "status" => "error",
                "message" => $this->response->status_description,
            ];
        }
    }

    public static function validateHashKey($hashKey, $secretKey)
    {
        $status = $currencyCode = "";
        $total = $invoiceId = $orderId = 0;

        if (!empty($hashKey)) {
            $hashKey = str_replace('_', '/', $hashKey);
            $password = sha1($secretKey);

            $components = explode(':', $hashKey);
            if (count($components) > 2) {
                $iv = isset($components[0]) ? $components[0] : "";
                $salt = isset($components[1]) ? $components[1] : "";
                $salt = hash('sha256', $password . $salt);
                $encryptedMsg = isset($components[2]) ? $components[2] : "";

                $decryptedMsg = openssl_decrypt($encryptedMsg, 'aes-256-cbc', $salt, null, $iv);

                if (strpos($decryptedMsg, '|') !== false) {
                    $array = explode('|', $decryptedMsg);
                    $status = isset($array[0]) ? $array[0] : 0;
                    $total = isset($array[1]) ? $array[1] : 0;
                    $invoiceId = isset($array[2]) ? $array[2] : '0';
                    $orderId = isset($array[3]) ? $array[3] : 0;
                    $currencyCode = isset($array[4]) ? $array[4] : '';
                }
            }
        }

        return [$status, $total, $invoiceId, $orderId, $currencyCode];
    }
}

if (!function_exists('dump')) {
    function dump($data)
    {
        echo '<pre>' . var_export($data, true) . '</pre>';
        /* highlight_string("<?php\n\$data =\n" . var_export($data, true) . ";\n?>"); */
    }
}

if (!function_exists('isJson')) {
    function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}
