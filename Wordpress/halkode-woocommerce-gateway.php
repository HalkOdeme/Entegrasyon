<?php
    /*
    Plugin Name: Halk Ödeme Hizmetleri Sanal Pos
    Plugin URI: https://www.parao.com.tr/
    Description: Woocommerce için Halk Ödeme Entegrasyonu
    Domain Path: /i18n/languages/
    Text Domain: halkode

    */
    if (!defined('ABSPATH')) {
        exit;
    }

    add_action('plugins_loaded', 'halkode_pos', 0);
    add_action('init', 'my_custom_public_page');
    add_action('wp_ajax_delete_halkode_card', 'delete_halkode_card');
    add_action('wp_ajax_get_installment', 'get_installment');
    add_action('wp_ajax_get_admin_installment', 'get_admin_installment');

    add_action('wp_ajax_nopriv_get_installment', 'get_installment');
    add_action('wp_ajax_nopriv_get_admin_installment', 'get_admin_installment');
    function halkode_pos()
    {
        //if condition use to do nothin while WooCommerce is not installed
        if (!class_exists('WC_Payment_Gateway')) {
            return;
        }
        include_once 'halkode-woocommerce.php';
        include_once 'halkode-woocommerce-recurring.php';
        // class add it too WooCommerce

        add_filter('woocommerce_payment_gateways', 'halkode_gateway');
        function halkode_gateway($methods)
        {
            $methods[] = 'halkode_sanalpos';
            return $methods;
        }
    }

    function delete_halkode_card()
    {
        global $wpdb;
        if (!empty($_POST['card'])) {
            $table = $wpdb->prefix . 'halkode_cards';;
            $wpdb->delete($table, array('card_token' => $_POST['card']));
        }
    }


// Add custom action links
    add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'halkode_settings');
    function halkode_settings($links)
    {
        $plugin_links = ['<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=halkode_sanalpos') . '">' . __('Settings', 'halkode_sanalpos') . '</a>'];
        return array_merge($plugin_links, $links);
    }

    function getToken()
    {
        $halkode_pay = new halkode_sanalpos();
        $api_secret = $halkode_pay->get_option('app_secret');
        $api_key = $halkode_pay->get_option('app_key');
        $merchant_key = $halkode_pay->get_option('merchant_key');
        $merchant_id = $halkode_pay->get_option('merchant_id');
        $sandbox = $halkode_pay->get_option('environment');


        $url = $sandbox == 'yes' ? 'https://testapp.halkode.com.tr/ccpayment/api/token' : 'https://app.halkode.com.tr/ccpayment/api/token';

        $array = [
            'app_id' => $api_key,
            'app_secret' => $api_secret
        ];

        return getCurl($url, 'POST', $array);

    }

    function checkStatus($invoice_id)
    {
        $halkode_pay = new halkode_sanalpos();
        $api_secret = $halkode_pay->get_option('app_secret');
        $api_key = $halkode_pay->get_option('app_key');
        $merchant_key = $halkode_pay->get_option('merchant_key');
        $merchant_id = $halkode_pay->get_option('merchant_id');
        $sandbox = $halkode_pay->get_option('environment');

        $hash_key = generateRefundHashKey($invoice_id, $merchant_key, $api_secret);
        $token = getToken()->data->token;
        $headers = ['Accept: application/json', 'Content-Type: application/json', "Authorization: Bearer {$token}"];
        $url = $sandbox == 'yes' ? 'https://testapp.halkode.com.tr/ccpayment/api/checkstatus' : 'https://app.halkode.com.tr/ccpayment/api/checkstatus';

        $array = [
            'invoice_id' => $invoice_id,
            'merchant_key' => $merchant_key,
            'hash_key' => $hash_key,
            'include_pending_status' => "true",
        ];

        return getCurl($url, 'POST', json_encode($array), $headers);
    }

    function generateRefundHashKey($invoice_id, $merchant_key, $app_secret)
    {
        $data = $invoice_id . '|' . $merchant_key;
        $iv = substr(sha1(mt_rand()), 0, 16);
        $password = sha1($app_secret);
        $salt = substr(sha1(mt_rand()), 0, 4);
        $saltWithPassword = hash('sha256', $password . $salt);
        $encrypted = openssl_encrypt(
            "$data", 'aes-256-cbc', "$saltWithPassword", null, $iv
        );
        $msg_encrypted_bundle = "$iv:$salt:$encrypted";
        $hash_key = str_replace('/', '__', $msg_encrypted_bundle);
        return $hash_key;
    }

    function my_custom_public_page()
    {

        global $woocommerce;
        if (isset($_GET['action']) and ($_GET['action'] == 'woocommerce_get_order_details' or $_GET['action'] == 'woocommerce_mark_order_status'))
            return '';

        if (isset($_GET['order_id']) && !isset($_GET['invoice_id'])) {


            $result = unserialize(base64_decode(get_post_meta($_GET['order_id'], 'halkode_payment_form', true)));



            if (!is_array($result)) {

                echo $result;
                delete_post_meta($_GET['order_id'], 'halkode_payment_form');

                exit;
            } else {
                if (isset($result['purchase']) && $result['purchase'] == 'yes') {
                    unset($result['token']);
                    unset($result['is_3d']);
                    unset($result['purchase']);
                    unset($result['installments_number']);
                    unset($result['transaction_type']);
                    unset($result['hash_key']);
                    unset($result['sale_web_hook_key']);

                    $new_form = $result;


                    $invoice['invoice_id'] = $result['invoice_id'];
                    $invoice['invoice_description'] = $result['invoice_description'];
                    $invoice['total'] = $result['total'];
                    $invoice['return_url'] = $result['return_url'];
                    $invoice['cancel_url'] = $result['cancel_url'];
                    $invoice['items'] = $result['items'];

                    unset($new_form['invoice_id']);
                    unset($new_form['invoice_description']);
                    unset($new_form['total']);
                    unset($new_form['return_url']);
                    unset($new_form['cancel_url']);
                    unset($new_form['items']);


                    $halkode_pay = new halkode_sanalpos();
                    $environment = $halkode_pay->get_option('environment') == "yes" ? 'TRUE' : 'FALSE';

                    $post = array(

                        'merchant_key' => $halkode_pay->get_option('merchant_key'),

                        'invoice' => json_encode($invoice),

                        'currency_code' => get_option('woocommerce_currency'),

                        'name' => $result['name'],

                        'surname' => $result['surname']

                    );


                    //print_r($post); exit;

                    $environment_url = "FALSE" == $environment ? 'https://app.halkode.com.tr/ccpayment/purchase/link' : 'https://testapp.halkode.com.tr/ccpayment/purchase/link';
                    $headers = ['Content-Type: application/json'];
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $environment_url);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post));
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

                    $response = json_decode(curl_exec($ch), true);

                    curl_close($ch);

                    if ($response['status'] == 1) {
                        echo("<script>location.href='" . $response['link'] . "'</script>");
                        exit;


                    } else {

                        wc_add_notice($response->status_description, 'error');

                        wp_redirect(wc_get_checkout_url() . '?error=' . $response->status_description);
                        exit;
                    }


                }
                $response = pay2d($result['token'], $result);



                if ($response->status_code == 100) {
                    $order_id = explode('WOO', $response->data->invoice_id);
                    $order_id = end($order_id);
                    $customer_order = new WC_Order($order_id);

                    $status = checkStatus($response->data->invoice_id);

                    if ($status->status_code == 100 || $status->status_code == 69) {
                        $customer_order->update_status('processing');
                        $customer_order->add_order_note(__('Sanal pos ödeme başarıyla alındı. Ödeme referans no :' . $status->order_id));
                        try {
                            WC()->mailer()->customer_invoice($customer_order);
                            $admin_email = WC()->mailer()->emails['WC_Email_New_Order'];
                            if ($admin_email) {
                                $admin_email->trigger($customer_order->get_id());
                            }

                        } catch (\Exception $e) {
                            error_log($e->getMessage());
                        }

                        delete_post_meta($order_id, 'halkode_payment_form');
                        delete_post_meta($order_id, 'halkode_response');
                        //echo $customer_order->get_checkout_order_received_url(); exit;
                        // paid order marked
                        // $customer_order->payment_complete();
                        // // this is important part for empty cart
                        // $woocommerce->cart->empty_cart();
                        // Redirect to thank you page
                        header('Location: ' . $customer_order->get_checkout_order_received_url());
                        exit;
                    } else {

                        update_post_meta($order_id, 'halkode_response', $response->status_description);
                        delete_post_meta($order_id, 'halkode_payment_form');
                        wc_add_notice($response->status_description, 'error');

                        wp_redirect(wc_get_checkout_url() . '?error=' . $response->status_description);
                        exit;
                    }


                } else {
                    $order_id = $_GET['order_id'];

                    update_post_meta($order_id, 'halkode_response', $response->status_description);
                    delete_post_meta($order_id, 'halkode_payment_form');
                    wc_add_notice($response->status_description, 'error');

                    wp_redirect(wc_get_checkout_url() . '?error=' . $response->status_description);
                    exit;
                }

            }

        }

        if (isset($_GET['webhook']) && $_GET['webhook'] == 1) {

            $order_id = explode('WOO', $_POST['invoice_id']);
            $customer_order = new WC_Order(end($order_id));
            if ($_POST['payment_status'] == '1') {
                $customer_order->update_status('processing');
                $customer_order->add_order_note(__('Sanal pos webhook araclığıyla ödeme onaylandı. Ödeme referans no :' . $_POST['order_no']));

            } else {
                $customer_order->update_status('failed');
                $customer_order->add_order_note(__('Webhook araclığıyla işlem iptal edildi'));
            }
        }

        if (isset($_GET['invoice_id']) and strstr($_GET['invoice_id'], 'WOO'))
            $order_id = explode('WOO', $_GET['invoice_id']);

        if (isset($_GET['invoice_id']) && $_GET['payment_status'] == 1) {
            $order_id = end($order_id);
            $customer_order = new WC_Order($order_id);
            $status = checkStatus($_GET['invoice_id']);

            if ($status->status_code == 100 || $status->status_code == 69) {

                $customer_order->update_status('processing');
                $customer_order->add_order_note(__('Sanal pos ödeme başarıyla alındı. Ödeme referans no :' . $status->order_id));
                try {
                    WC()->mailer()->customer_invoice($customer_order);
                    $admin_email = WC()->mailer()->emails['WC_Email_New_Order'];
                    if ($admin_email) {
                        $admin_email->trigger($customer_order->get_id());
                    }

                } catch (\Exception $e) {
                    error_log($e->getMessage());
                }


                delete_post_meta($order_id, 'halkode_payment_form');
                delete_post_meta($order_id, 'halkode_response');
                //echo $customer_order->get_checkout_order_received_url(); exit;
                // paid order marked
                // $customer_order->payment_complete();
                // // this is important part for empty cart
                // $woocommerce->cart->empty_cart();
                // Redirect to thank you page
                header('Location: ' . $customer_order->get_checkout_order_received_url());
            } else {

                update_post_meta($order_id, 'halkode_response', $response->status_description);
                delete_post_meta($order_id, 'halkode_payment_form');
                wc_add_notice($response->status_description, 'error');

                wp_redirect(wc_get_checkout_url() . '?error=' . $response->status_description);
                exit;
            }
        } elseif (isset($_GET['payment_status']) && $_GET['payment_status'] == 0) {
            $order_id = end($order_id);

            update_post_meta($order_id, 'halkode_response', $_GET['error']);
            delete_post_meta($order_id, 'halkode_payment_form');
            wc_add_notice($_GET['error'], 'error');

        }
    }


    function get_installment()
    {

        if (!empty($_POST['cc_number'])) {
            global $woocommerce;

            $halkode_pay = new halkode_sanalpos();

            /* getpos request */

            $pos_post = [
                'credit_card' => $_POST['cc_number'],

                'amount' => $woocommerce->cart->total,

                "currency_code" => get_option('woocommerce_currency'),

                "merchant_key" => $halkode_pay->get_option('merchant_key'),

                'app_id' => $halkode_pay->get_option('app_key'),

                'app_secret' => $halkode_pay->get_option('app_secret'),
            ];

            $environment = $halkode_pay->get_option('environment') == "yes" ? 'TRUE' : 'FALSE';
            $environment_url = "FALSE" == $environment ? 'https://app.halkode.com.tr/ccpayment/api/getpos' : 'https://testapp.halkode.com.tr/ccpayment/api/getpos';


            if (!empty($_POST['recurring_options']['recurring_check']) && $_POST['recurring_options']['recurring_check'] == 'yes') {
                $pos_post['is_recurring'] = 1;
            }

            $headers = ['Accept: application/json', 'Content-Type: application/json', "Authorization: Bearer {$_POST['token']}"];
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $environment_url);

            curl_setopt($ch, CURLOPT_POST, true);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($pos_post));

            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            $get_pos_response = json_decode(curl_exec($ch), true);


            //print_r($get_pos_response); exit;
            curl_close($ch);
            if ($get_pos_response['status_code'] == 100) {
                $html = '';

                if (!empty($get_pos_response['data'])) {
                    $pos_id = '';

                    $pos_amt = '';

                    $currency_id = "";

                    $campaign_id = "";

                    $allocation_id = "";

                    $installments_number = "";

                    $hash_key = "";

                    $currency_code = '';

                    $i = 0;

                    $html = "<div class='row'>";

                    $installments_count = count($get_pos_response['data']);

                    foreach ($get_pos_response['data'] as $val) {

                        if (!in_array($val['installments_number'], $halkode_pay->get_option('installments'))) {
                            $i++;
                            continue;
                        }


                        $active_cls = "";

                        //                      $inst= ($i+1)." Installment";

                        $currency_code = $val['currency_code'];

                        if ($i == 0) {
                            $active_cls = 'active';

                            $pos_id = $val['pos_id'];

                            $pos_amt = $val['amount_to_be_paid'];

                            $currency_id = $val['currency_id'];

                            $campaign_id = $val['campaign_id'];

                            $allocation_id = $val['allocation_id'];

                            $installments_number = $val['installments_number'];
                            $hash_key = $val['hash_key'];

                            $inst = $halkode_pay->getLocalizationContent('single_installment', $currency_code);
                        } else {
                            $inst = $i + 1 . " " . $halkode_pay->getLocalizationContent('installment', $currency_code);
                        }

                        $html .=
                            "<div class='single-installment " .
                            $active_cls .
                            "' data-posid='" .
                            $val["pos_id"] .
                            "' data-amount='" .
                            $val["amount_to_be_paid"] .
                            "' data-currency_id='" .
                            $val["currency_id"] .
                            "' data-campaign_id='" .
                            $val["campaign_id"] .
                            "' data-allocation_id='" .
                            $val["allocation_id"] .
                            "' data-installments_number='" .
                            $val["installments_number"] .
                            "' data-hash_key='" .
                            $val["hash_key"] .
                            "' data-currency_code='" .
                            $val["currency_code"] .
                            "'>

                        <div class='halkode_heading'>" .
                            $inst .
                            "</div>

                        <div class='halkode_amount'>" .
                            $val['amount_to_be_paid'] .
                            " " .
                            $val['currency_code'] .
                            "</div>

                        <div class='halkode_installment_number'>" .
                            ($i + 1) .
                            " X</div>

                        <div class='halkode_total_amount'>" .
                            number_format($val['amount_to_be_paid'] / ($i + 1), 2) .
                            " " .
                            $val['currency_code'] .
                            "</div></div>";

                        $i++;
                    }

                    $html .= "</div>";

                    echo json_encode([
                        'data' => $html,
                        'pos_id' => $pos_id,
                        'pos_amt' => $pos_amt,
                        'currency_id' => $currency_id,
                        'campaign_id' => $campaign_id,
                        'allocation_id' => $allocation_id,
                        'installments_number' => $installments_number,
                        'hash_key' => $hash_key,
                        'currency_code' => $currency_code,
                    ]);

                    exit();
                }
            }
        }

        /* end getpos request */

        echo json_encode(['data' => '', 'pos_id' => '', 'pos_amt' => '', 'currency_id' => '', 'campaign_id' => '', 'allocation_id' => '', 'installments_number' => '', 'hash_key' => '', 'currency_code' => '']);

        exit();
    }


    function pay2d($token, $parameters)
    {


        $halkode_pay = new halkode_sanalpos();
        $environment = $halkode_pay->get_option('environment') == "yes" ? 'TRUE' : 'FALSE';
        if ($parameters['is_2d_card'] == 'yes') {
            $environment_url = "FALSE" == $environment ? 'https://app.halkode.com.tr/ccpayment/api/payByCardTokenNonSecure' : 'https://testapp.halkode.com.tr/ccpayment/api/payByCardTokenNonSecure';

        } else {
            $environment_url = "FALSE" == $environment ? 'https://app.halkode.com.tr/ccpayment/api/paySmart2D' : 'https://testapp.halkode.com.tr/ccpayment/api/paySmart2D';

        }

        $headers = ['Accept: application/json', 'Content-Type: application/json', "Authorization: Bearer $token"];


        $options = array(
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_VERBOSE => false,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($parameters),
            //CURLOPT_SSL_VERIFYHOST => 0,
            //CURLOPT_SSL_VERIFYPEER => 0,
        );

        $ch = curl_init($environment_url);
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);

        $err = curl_errno($ch);
        $errmsg = curl_error($ch);
        $header = curl_getinfo($ch);
        $rurl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        curl_close($ch);


        return json_decode($content);
    }

    function getCurl($url, $method, $array, $header = [])
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $array,
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        return json_decode($response);
    }

    
