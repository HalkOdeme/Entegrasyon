<?php

function dump($x){
    echo "<pre>";
    print_r($x);
    echo "</pre>";
}

class Controllerextensionpaymenthalkodepayment extends Controller{
    public function recurringCancel() {
        return "";
    }

    public function index()
    {
        $this->load->model('checkout/order');
        $this->language->load('extension/payment/halkodepayment');
        $this->load->model('extension/payment/halkodepayment');

        $data['text_credit_card']     = $this->language->get('text_credit_card');
        $data['text_use_stored_card']     = $this->language->get('text_use_stored_card');
        $data['text_wait']            = $this->language->get('text_wait');
        $data['entry_cc_owner']       = $this->language->get('entry_cc_owner');
        $data['entry_cc_number']      = $this->language->get('entry_cc_number');
        $data['entry_cc_expire_date'] = $this->language->get('entry_cc_expire_date');
        $data['entry_cc_cvv2']        = $this->language->get('entry_cc_cvv2');
        $data['entry_cc_cvv2_desc']   = $this->language->get('entry_cc_cvv2_desc');
        $data['entry_cc_cvv2_desc2']  = $this->language->get('entry_cc_cvv2_desc2');
        $data['make_payment']         = $this->language->get('make_payment');
        $data['text_no_have_stored_cards']         = $this->language->get('text_no_have_stored_cards');
        $data['vade_text']   = $this->language->get('vade_text');
        $data['aylik_text']  = $this->language->get('aylik_text');
        $data['toplam_text'] = $this->language->get('toplam_text');
        $data['pesin_text']  = $this->language->get('pesin_text');
        $data['text_use_3d']  = $this->language->get('text_use_3d');
        $data['text_wait']      = $this->language->get('text_wait');
        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['button_back']    = $this->language->get('button_back');

        $data['no_use_saved_card']    = $this->language->get('no_use_saved_card');
        $data['button_back']    = $this->language->get('button_back');

        $data['entry_delete']    = $this->language->get('entry_delete');
        $data['entry_delete_url'] = $this->url->link('extension/payment/halkodepayment/deletemycard', '', true);
        

    

        $this->halkode = new halkodepayment(
            $this->config->get('payment_halkodepayment_app_key'),
            $this->config->get('payment_halkodepayment_app_secret'),
            $this->config->get('payment_halkodepayment_merchant_key'),
            $this->config->get('payment_halkodepayment_sale_web_hook_key'),
            $this->config->get('payment_halkodepayment_recurring_web_hook_key'),
            $this->config->get('payment_halkodepayment_environment'),
            $this->config->get('payment_halkodepayment_debug')
        );

        $this->halkode->getToken();
        //var_dump($this->halkode);exit;
        $returnurl = $this->url->link('extension/payment/halkodepayment/process', '', true);
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $grandTotal = $order_info['total'];
        $data['total'] = $this->cart->getTotal(); // + shipping
        $data['total'] = $grandTotal;

        if ($order_info["currency_code"] != "") {
            $currency_code = $order_info["currency_code"];
        } else {
            $currency_code = "TRY";
        }

        $currency_rate = $order_info["currency_value"]; //1.000 değilse farklı kur

        $order_total = number_format($order_info["total"] * $currency_rate, 2, '.', '');


        if ($this->halkode->is_3d == 4 OR $this->halkode->is_3d == 8) {
            // redirect
            $data["mode"]         = "redirect";
            $data["redirect_url2"]         = $this->url->link('extension/payment/halkodepayment/process', '', true);
        } else {
            $months = [];
            for ($i = 1; $i <= 12; $i++) {
                $months[] = sprintf("%02d", $i);
            }
            $years = [];
            for ($i = 0; $i <= 10; $i++) {
                $years[] = date('Y', strtotime('+' . $i . ' years'));
            }

            $savedCards = [];
            $halkodeSavedCards = $this->halkode->getStoredCards($this->session->data['user_id']);
            if( $halkodeSavedCards["status"] == "success" AND count($halkodeSavedCards["cards"]) ){
                $cards = $halkodeSavedCards["cards"];
                foreach ($cards as $card)
                    $savedCards[ $card->card_token ] = $card->card_number;
            }

            // form
            $data['card_store_feature'] = 0;
            if ( $this->customer->isLogged() ) {
                $data['card_store_feature'] = 1;
            }
            
            $data["halkode_show_3d_option"] = $this->halkode->is_3d == 1;
            $data["months"]     = $months;
            $data["years"]      = $years;
            $data["savedCards"] = $savedCards;
            $data["mode"]       = "form";
            $data["form_url"]   = $this->url->link('extension/payment/halkodepayment/process', '', true);
        }
        
        //$this->document->addScript('view/javascript/halkodepayment.js');
        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/halkodepayment.twig')) {
            $this->template = $this->config->get('config_template') . '/template/payment/halkodepayment.twig';
        } else {
            $this->template = 'default/template/payment/halkodepayment.twig';
        }
        return $this->load->view('extension/payment/halkodepayment', $data);
    }

    public function ajax(){
        $this->load->model('checkout/order');
        $this->language->load('extension/payment/halkodepayment');
        $this->load->model('extension/payment/halkodepayment');
        // bin installment list
        $data['toplam_text'] = $this->language->get('toplam_text');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $currency_rate = $order_info["currency_value"]; //1.000 değilse farklı kur
        $cart_total = $this->cart->getTotal() * $currency_rate;
        $cart_products = $this->cart->getProducts();
        
        $grandTotal = $order_info['total'];
        $data['total'] = $this->cart->getTotal(); // + shipping
        $total = $grandTotal;

        if( $this->request->get['getInstallments'] ){
            $card_number = $this->request->post['card'];
            $card_number = str_replace(' ', '', $card_number);

            if( strlen( $card_number ) > 6 )
                $card_number = substr($card_number, 0, 6);

            $halkode  = new halkodepayment(
                $this->config->get('payment_halkodepayment_app_key'),
                $this->config->get('payment_halkodepayment_app_secret'),
                $this->config->get('payment_halkodepayment_merchant_key'),
                $this->config->get('payment_halkodepayment_sale_web_hook_key'),
                $this->config->get('payment_halkodepayment_recurring_web_hook_key'),
                $this->config->get('payment_halkodepayment_environment'),
                $this->config->get('payment_halkodepayment_debug')
            );

            $halkode->debug = false;
            $rates = $halkode->getInstallments($total, $order_info["currency_code"], $card_number);
            //dump($rates);
            $halkode_installments = explode(",", $this->config->get('payment_halkodepayment_installments'));
            $halkode_installments[] = 1;

            if( $rates["status"] != "error" AND count($rates["data"] ) ){
                foreach( $rates["data"] as $bankTaksit ){
                    $vade  = $bankTaksit->installments_number;
                    if( !in_array($vade, $halkode_installments) )
                        continue;
                    $toplam = $bankTaksit->amount_to_be_paid;
                    $aylik = number_format( $toplam / $vade , 2);
                    $taksitler[ $vade ] = [
                        'taksit' => $vade,
                        'aylik'  => $aylik,
                        'toplam' => $toplam,
                        'text'   => $vade . " x " . $aylik . ", " . $data['toplam_text'] . " : ". $toplam
                    ];
                }
            }

            echo json_encode(["status" => "success", "taksitler" => $taksitler]);
            exit;
        }
    }

    public function process(){
        $this->load->model('extension/total/coupon');
        $this->load->model('checkout/order');
        $this->language->load('extension/payment/halkodepayment');
        $this->load->model('extension/payment/halkodepayment');
        // bin installment list
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $currency_rate = $order_info["currency_value"]; //1.000 değilse farklı kur
        $cart_total = $this->cart->getTotal() * $currency_rate;
        $cart_products = $this->cart->getProducts();
        //dump([$cart_products, $order_info]);exit;
        $grandTotal = $order_info['total'];
        $data['total'] = $this->cart->getTotal(); // + shipping
        $total = $grandTotal;

        if ($order_info["currency_code"] != "") {
            $currency_code = $order_info["currency_code"];
        } else {
            $currency_code = "TRY";
        }

        $halkode = new halkodepayment(
            $this->config->get('payment_halkodepayment_app_key'),
            $this->config->get('payment_halkodepayment_app_secret'),
            $this->config->get('payment_halkodepayment_merchant_key'),
            $this->config->get('payment_halkodepayment_sale_web_hook_key'),
            $this->config->get('payment_halkodepayment_recurring_web_hook_key'),
            $this->config->get('payment_halkodepayment_environment'),
            $this->config->get('payment_halkodepayment_debug')
        );

        $halkode->getToken();
        $halkode->return_url = $this->url->link('extension/payment/halkodepayment/validation', '', true);
        $halkode->cancel_url = $this->url->link('extension/payment/halkodepayment/validation', '', true);

        $items = [];
        $productRecurrings = [];
        $itemTotal = 0;
        foreach ($cart_products as $product) {

            $x = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $currency_rate;
            $itemAmount = floatval(number_format($x, 2, '.', ''));

            if( isset($product["recurring"]) AND $productRecurring = $product["recurring"] )
                $productRecurrings[] = $productRecurring;

            $halkode->items[] = array(
                "code" => ($product["product_id"]),
                //"name"=> $product["name"],
                "product_name" => substr($product["name"], 0, 60),  //"name"=> substr($product["name"] , 0 , 60),
                "description" => "",
                "product_quantity" => intval( $product["quantity"] ),
                "product_price" => $itemAmount,
                //"recurring" => $productRecurring
            );
            $itemTotal += $itemAmount * intval( $product["quantity"] );
        }
        $recurring_payment = false;
        $isSame = true;
        if (count($productRecurrings) > 0) {
            
            $recurring_payment = true;
            $types = array_map('gettype', $productRecurrings);
            if (!$this->same($types)) {
                $isSame = false;
            }
            if ($isSame) {
                foreach ($productRecurrings as $productRecurring) {
                    if ($productRecurring != $productRecurrings[0]) {
                        $isSame = false;
                    }
                }
            }
        }

        if (!$isSame) {
            $this->session->data['error'] = "Sipariş edilen ürünlerin hepsi aynı tekrarlı ödeme değerine sahip olması gerekir";
            $this->response->redirect($this->url->link('checkout/checkout', '', true));
        }

        if ($recurring_payment) {
            if( $productRecurrings[0]["frequency"] == 'day' )
                $cycle = 'D';
            elseif( $productRecurrings[0]["frequency"] == 'week' )
                $cycle = 'W';
            elseif( $productRecurrings[0]["frequency"] == 'month' )
                $cycle = 'M';
            elseif( $productRecurrings[0]["frequency"] == 'year' )
                $cycle = 'Y';

            $halkode->order["recurring"] = 1;
            $halkode->order["recurring_payment_number"] = $productRecurrings[0]["duration"];
            $halkode->order["recurring_payment_cycle"] = $cycle;
            $halkode->order["recurring_payment_interval"] = $productRecurrings[0]["cycle"];
            $halkode->order["recurring_web_hook_key"] = $this->config->get('payment_halkodepayment_recurring_web_hook_key');

            // store sub payments for purchase if not created before
            /* @TODO
            if (!halkodeRecurringOrder::orderExist($cart->id)) {
                for ($i = 1; $i <= $halkode->order["recurring_payment_number"]; $i++) {
                    $model = new halkodeRecurringOrder();
                    $model->id_order = $cart->id;
                    $model->payment_number = $i;
                    $model->save();
                }
            }
            */
        }
        
        if( isset($this->session->data['shipping_method']) ){
            $shippingTotal = $this->session->data['shipping_method']['cost'];
            if ( $shippingTotal > 0) {
                $halkode->items[] = array(
                    "code" => "9191",
                    "product_name" => "Kargo Ücreti",
                    "description" => "Kargo",
                    "product_quantity" => 1,
                    "product_price" => floatval(number_format(abs($shippingTotal), 2, '.', ''))
                );
                $itemTotal += floatval(number_format(abs($shippingTotal), 2, '.', ''));
            }
        }
        

        $halkode->order["key"] = $this->session->data['order_id'];
        $halkode->order["sub_total"] = number_format($grandTotal, 2, '.', '');
        $halkode->order["total"] = number_format($grandTotal, 2, '.', '');//$this->request->post['payment_halkode_total'];
        
        //$coupon_info = $this->model_extension_total_coupon->getCoupon($this->session->data['coupon']);
        if (isset($this->session->data['coupon'])) {
            $coupon_info = $this->model_extension_total_coupon->getCoupon($this->session->data['coupon']);
        } else {
            $coupon_info = null;
        }

        
        //dump( [$order_info, $halkode->order['total'], $itemTotal] );exit;
        $discount = 0;
        if( $halkode->order['total'] < $itemTotal )
            $discount = abs($itemTotal - $halkode->order['total']);



        $halkode->order["discount"] = $discount;// @TODO number_format($order_info->getOrderTotal(true, Cart::ONLY_DISCOUNTS), 2, '.', '');
        $halkode->order["installment"] = $this->request->post['installmentCount'] == "" ? "1" : $this->request->post['installmentCount'];
        
        $halkode->order["currency"] = $currency_code; //KUR
        $halkode->order["transaction_type"] = $this->config->get("payment_halkodepayment_provision");

        $halkode->paymentid = $this->session->data['order_id'] . "-" . date('dmY') . "-" . rand(100, 999);
        $this->session->data['halkode_paymentid'] = $halkode->paymentid;
        $phone = $order_info["telephone"];
        $halkode->customer = array(
            'id' => $this->session->data['user_id'],
            'name' => $order_info["firstname"] . " " . $order_info["lastname"],
            'firstname' => $order_info["firstname"],
            'lastname' => $order_info["lastname"],
            'email' => (string) $order_info["email"],
            'phone' => $order_info["telephone"],
        );
        $pan = $new_str = str_replace(' ', '', $this->request->post['pan']);
        list($ay, $yil) = explode('/', $this->request->post['expirationdate']);
        $halkode->card = [
            "owner" => $this->request->post['cardOwner'],
            "pan" => $pan,
            "month" => $ay,
            "year" => $yil,
            "cvc" => $this->request->post['cvv']
        ];
        
        $halkode->billing = array(
            'email' => (string) $order_info["email"],
            'address' => $order_info["payment_address_1"] . " " . $order_info["payment_address_2"],
            'address1' => $order_info["payment_address_1"],
            'address2' => $order_info["payment_address_2"],
            'city' => $order_info["payment_city"],
            'country' => $order_info["payment_country"],
            'state' =>  $order_info["payment_zone"],
            'postcode' =>  $order_info["payment_postcode"],
            'phone' => $order_info["telephone"],
        );
        $halkode->shipping = array(
            'address' => $order_info["shipping_address_1"] . " " . $order_info["shipping_address_2"],
            'city' => $order_info["shipping_city"],
            'country' => $order_info["shipping_country"],
            'zip' => $order_info["shipping_postcode"],
            'phone' => $order_info["telephone"],
        );

        // save card ?
        $pan = $new_str = str_replace(' ', '', $this->request->post['pan']);
        list($ay, $yil) = explode('/', $this->request->post['expirationdate']);
        if ( isset($this->request->post['saveCard']) ) {
            $actionStoreCard = $halkode->storeCard(
                $this->session->data['user_id'],
                $this->request->post['cardOwner'],
                $pan,
                $ay,
                $yil,
                $order_info["firstname"] . " " . $order_info["lastname"],
                $order_info["telephone"]
            );
            if ($actionStoreCard["status"] != "success") {
                if( $halkode->debug == '1' ){
                    //dump($actionStoreCard);exit;
                }
            }
            //dump($actionStoreCard);exit;
        }
 
            if ($halkode->is_3d == 4  OR ($this->halkode && $this->halkode->is_3d == 8) ) {
                $halkodeForm = $halkode->generatePaymentLink();
                $mode = "redirect";
                //dump($halkodeForm);exit;
            } else {
                $formmethod = 'POST';
                $mode = "httppost";
                //dump( $halkode );exit;
                if( isset($this->request->post['useCard']) AND $this->request->post['useCard'] != "0" )
                    $halkodeForm = $halkode->generateSavedCardForm($this->request->post['useCard']);
                elseif ( $halkode->is_3d == 2 OR ( $halkode->is_3d == 1 AND isset($this->request->post['use3d']) )  )
                    $halkodeForm = $halkode->generate3DForm();
                else{
                    $mode = "redirect";
                    $halkodeForm = $halkode->generate2DForm();
                }
                
            }
 

        //dump( $halkodeForm );exit;

        if ($halkodeForm["status"] == "success") {
            if ($mode == "httppost") {
                $httpForm = $halkodeForm["form"];
                $data["form"] = $httpForm;
                $data["form"]["method"] = $formmethod;
                $data["debug"] = $halkode->debug;
                
                return $this->response->setOutput(
                    $this->load->view('extension/payment/halkodepayment_execution', $data));
                
            } elseif ($mode = "redirect") {
                if($halkode->debug){
                    $asi = $halkodeForm["redirect"];
                    $redirectHtml = '<script>window.setTimeout(function () {
        location.href = "'.$asi.'";
    }, 5000);</script>';
                    
                    echo $redirectHtml;
                    }
                $this->response->redirect($halkodeForm["redirect"]);
                exit;
                
            }
        } else {
            $this->session->data['error'] = $halkodeForm["message"];
            $this->response->redirect($this->url->link('checkout/checkout', '', true));
        }    
    }

    public function validation(){
        $this->load->model('checkout/order');
        $this->language->load('extension/payment/halkodepayment');
        $this->load->model('extension/payment/halkodepayment');



        if (!isset($this->session->data['order_id'])) {
            echo "Tarayıcınızın SameSite ayarındaki hatadan dolayı yönlendirme sonrası oturumunuz kapandı. Başka bir tarayıcı deneyebilir veya SameSite ayarınızı değiştirerek tekrar deneyebilirsiniz.";
            exit;
        }
        
        $halkode = new halkodepayment(
            $this->config->get('payment_halkodepayment_app_key'),
            $this->config->get('payment_halkodepayment_app_secret'),
            $this->config->get('payment_halkodepayment_merchant_key'),
            $this->config->get('payment_halkodepayment_sale_web_hook_key'),
            $this->config->get('payment_halkodepayment_recurring_web_hook_key'),
            $this->config->get('payment_halkodepayment_environment'),
            $this->config->get('payment_halkodepayment_debug')
        );

        
        //dump( [$order_info, $_POST, $_GET, $x, $this->session->data['halkode_paymentid']] );exit;

        //$this->model_checkout_order->update($this->session->data['order_id'], $this->config->get('web_payment_software_order_status_id'), $message, false);

        
        $p = $this->request->post;
        //dump($this->request->get);exit;
        $hashControl = 0;
        if( $hashControl ){
            $x = halkodepayment::validateHashKey( $this->request->get['hash_key'], $this->config->get('payment_halkodepayment_app_secret') );
            if( ($x[0] != $this->request->get["halkode_status"]) OR ($x[2] != $this->request->get["invoice_id"]) ){
            $this->session->data['error'] = "Sipariş işlemi tamamlanamadı. Hash kodu uyumlu değil.";
            if( $this->config->get('payment_halkodepayment_debug') ){
                dump([$this->session->data['error'], $x, $this->request->get]);
                exit;
            }
            $this->response->redirect($this->url->link('checkout/checkout', '', true));   
            }
        }
        

        if ($this->request->get["status_code"] != '100') {
            $this->session->data['error'] = "Ödeme İşlemi Tamamlanamadı. (" . $this->request->get["status_code"] . " : " . $this->request->get["status_description"] . ")";
            $this->response->redirect($this->url->link('checkout/checkout', '', true));
        }

        $message = "Kredi Kartı Ödeme Başarılı";

        $this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_halkodepayment_order_status_id'));

            
        $object                = new stdClass();
        $object->order_id      = $this->session->data['order_id'];//isset($order_info["order_id"]) ? $order_info["order_id"] : 1;
        $object->status        = '';
        $object->amount        = '';
        $object->currency      = '';
        $object->type          = '';
        $object->reference     = $this->session->data['halkode_paymentid'];
        $object->operation     = '';
        $object->transactionId = $this->session->data['halkode_paymentid'];
        $object->message       = $this->request->get['error'];
        $object->code          = $this->request->get['status_code'];
        $object->purchase_url  = '';
        $this->model_extension_payment_halkodepayment->appendi((array) $object);
            
        sleep(1);
        $this->response->redirect($this->url->link('checkout/success', '', true));
    }

    public function webhook(){

                $this->load->model('checkout/order');
        $this->language->load('extension/payment/halkodepayment');
        $this->load->model('extension/payment/halkodepayment');

        $p = $this->request->get + $this->request->post;

        if( trim($this->config->get('payment_halkodepayment_token')) != trim($p['token']) )
            die("401");

        $explode = explode('_', $p['invoice_id'] );
        $order_id = $explode[0];

        $halkode = new halkodepayment(
            $this->config->get('payment_halkodepayment_app_key'),
            $this->config->get('payment_halkodepayment_app_secret'),
            $this->config->get('payment_halkodepayment_merchant_key'),
            $this->config->get('payment_halkodepayment_sale_web_hook_key'),
            $this->config->get('payment_halkodepayment_recurring_web_hook_key'),
            $this->config->get('payment_halkodepayment_environment'),
            $this->config->get('payment_halkodepayment_debug')
        );

        $p = (array) $halkode->checkStatus( $order_id );

        if( $this->request->get['do'] == 'sale' ){

        }elseif( $this->request->get['do'] == 'refund' ){
            
                $this->model_checkout_order->addOrderHistory(
                    $order_id, 
                    11, 
                    $p['invoice_id'], 
                    false);
            

        }elseif( $this->request->get['do'] == 'recurring' ){

        }

        $object                = new stdClass();
        $object->order_id      = $order_id;//isset($order_info["order_id"]) ? $order_info["order_id"] : 1;
        $object->status        = '';
        $object->amount        = '';
        $object->currency      = '';
        $object->type          = $p['do'];
        $object->reference     = $p['invoice_id'];
        $object->operation     = '';
        $object->transactionId = $p['invoice_id'];
        $object->message       = $p['error'] ?: $p['message'];
        $object->code          = $p['status_code'];
        $object->purchase_url  = '';
        $this->model_extension_payment_halkodepayment->appendi((array) $object);
    }

    public function deletemycard(){

        $this->halkode = new halkodepayment(
            $this->config->get('payment_halkodepayment_app_key'),
            $this->config->get('payment_halkodepayment_app_secret'),
            $this->config->get('payment_halkodepayment_merchant_key'),
            $this->config->get('payment_halkodepayment_sale_web_hook_key'),
            $this->config->get('payment_halkodepayment_recurring_web_hook_key'),
            $this->config->get('payment_halkodepayment_environment'),
            $this->config->get('payment_halkodepayment_debug')
        );

        $cardToken = $this->request->get['card_token'];

        $this->halkode->deleteStoredCard( $cardToken , $this->session->data['user_id'] ); 

        $redirectHtml = '<script>
            setTimeout(function(){
                window.location.href = "'.$_SERVER['HTTP_REFERER'].'"
            }, 1000);
        </script>';

        die( $redirectHtml );


    }
    
    public function same($arr){
        return $arr === array_filter($arr, function ($element) use ($arr) {
                return ($element === $arr[0]);
            });
    }
}
