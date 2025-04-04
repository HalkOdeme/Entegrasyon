<?php

    if (!defined('ABSPATH')) {
        exit;
    }

    class halkode_sanalpos extends WC_Payment_Gateway
    {

        protected $is_3d = 0;
        public $headers = array(

            'Accept: application/json',

            'Content-Type: application/json'

        );

        function __construct()
        {
            // global ID
            $this->id = "halkode_sanalpos";
            // Show Title
            $this->method_title = __("Halk Ödeme Hizmetleri", 'halkode');
            // Show Description
            $this->method_description = __("Woocommerce için Halk Ödeme Hizmetleri Entegrasyonu", 'halkode');
            // vertical tab title
            $this->title = __("Halk Ödeme Hizmetleri Pos", 'halkode');
            $this->icon = null;
            $this->has_fields = true;
            // support default form with credit card
            //$this->supports = ['default_credit_card_form'];
            // setting defines
            $this->init_form_fields();
            // load time variable setting
            $this->init_settings();


            // Turn these settings into variables we can use
            foreach ($this->settings as $setting_key => $value) {
                $this->$setting_key = $value;
            }

            // further check of SSL if you want
            add_action('admin_notices', [$this, 'do_ssl_check']);
            add_action('wp_enqueue_scripts', array($this, 'payment_scripts'));

            // Save settings
            if (is_admin()) {
                $this->activate();
                add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
            }
        } // Here is the  End __construct()

        // administration fields for specific Gateway

        function activate()
        {
            global $wpdb;

            $table_name = $wpdb->prefix . 'postmeta'; // Tablo adını veritabanı önekini ekleyerek oluşturun

            $wpdb->query("DELETE FROM $table_name WHERE meta_key='halkode_data_payment'");
            $wpdb->query("DELETE FROM $table_name WHERE meta_key='halkode_payment_form'");

            $table_name = $wpdb->prefix . 'halkode_cards';

            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE IF NOT EXISTS $table_name (
                card_id int(11) NOT NULL AUTO_INCREMENT,
                customer_id INT(11) NOT NULL,
                card_token varchar(255) NOT NULL,
                card_mask varchar(255) NOT NULL,
                created_at  timestamp DEFAULT current_timestamp,
              PRIMARY KEY (card_id)
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            try {
                dbDelta($sql);
            } catch (\Exception $e) {
                error_log($e->getMessage());
            }


        }

        public function init_form_fields()
        {

            $installments = $this->get_admin_installment();
            $this->form_fields = [

                'merchant_key' => [
                    'title' => __('Merchant Key', 'halkode'),
                    'type' => 'text',
                    'desc_tip' => __('Merchant Key', 'halkode'),
                ],
                'app_key' => [
                    'title' => __('App Key', 'halkode'),
                    'type' => 'text',
                    'desc_tip' => __('App key', 'halkode'),
                ],
                'app_secret' => [
                    'title' => __('App Secret', 'halkode'),
                    'type' => 'text',
                    'desc_tip' => __('App Secret', 'halkode'),
                ],
                'merchant_id' => [
                    'title' => __('Merchant ID', 'halkode'),
                    'type' => 'text',
                    'desc_tip' => __('Merchant ID', 'halkode'),
                ],
                'sale_webhook_key' => [
                    'title' => __('Satış Webhook Anahtarı', 'halkode'),
                    'type' => 'text',
                    'desc_tip' => __('Satış Webhook Anahtarı', 'halkode'),
                    'description' => get_site_url() . '?webhook=1',
                ],
                'recurring_sale_webhook_key' => [
                    'title' => __('Yinelenen Satış Webhook Anahtarı', 'halkode'),
                    'type' => 'text',
                    'desc_tip' => __('Yinelenen Satış Webhook Anahtarı', 'halkode'),
                    'description' => get_site_url() . '?webhook=1&recurring=1',
                ],
                'environment' => [
                    'title' => __('Test Modu', 'halkode'),
                    'label' => __('Etkinleştir', 'halkode'),
                    'type' => 'checkbox',
                    'description' => __('Test ortamında deneme yapmak için etkinleştirin', 'halkode'),
                    'default' => 'no',
                ],
                'transaction_type' => array(
                    'title' => __('Provizyon Türü', 'halkode'),
                    'type' => 'select',
                    'default' => 'Auth',
                    'options' => array('Auth' => __('Auth', 'halkode'), 'PreAuth' => __('PreAuth', 'halkode'))
                ),
                'installments' => array(
                    'title' => 'Taksit Sayısı',
                    'type' => 'multiselect',
                    'options' => $installments,
                    'description' => __('Shift ile çoklu seçim yapabilirsiniz.', 'halkode'),
                    'custom_attributes' => array(
                        'data-placeholder' => __('Taksit Seçiniz', 'halkode'),
                    ),
                ),
                'enabled' => [
                    'title' => __('Ödeme Yöntemi <br> Etkin/Pasif', 'halkode'),
                    'label' => __('Bu yöntemi etkinleştir', 'halkode'),
                    'type' => 'checkbox',
                    'default' => 'no',
                ],
                'title' => [
                    'title' => __('Başlık', 'halkode'),
                    'type' => 'text',
                    'desc_tip' => __('Başlık', 'halkode'),
                    'default' => __('Halk Ödeme Hizmetleri', 'halkode'),
                ],
                'description' => [
                    'title' => __('Açıklama', 'halkode'),
                    'type' => 'textarea',
                    'desc_tip' => __('Açıklama', 'halkode'),
                    'default' => __('Kredi kartıyla ödeme yap', 'halkode'),
                    'css' => 'max-width:450px;',
                ],


            ];
        }

        public function getLocalizationContent($content, $language)
        {
            $language = strtoupper($language);

            $lang = [
                get_option('woocommerce_currency') => [
                    'card_holder_name' => 'Kart Sahibi',

                    'card_number' => 'Kart Numarası',

                    'expiry' => 'Son Kullanma Tarihi',

                    'cvv' => 'Güvenlik Numarası',

                    'single_installment' => 'Peşin',

                    'installment' => 'Taksit',

                    'new_card' => 'Yeni Kart',

                    'saved_card' => 'Kayıtlı Kart',

                    'save_card' => 'Bu Kartı Kaydet',

                    '3D_payment' => '3D Ödeme',

                    'your_registered_cards' => 'Kayıtlı Kartlarınız',

                    'choose_card' => 'Kart Seçiniz',

                    'no_registered_card' => 'Kayıtlı Kartınız Yok',

                    'delete_saved_card' => 'Sil',
                ],

                'USD' => [
                    'card_holder_name' => 'Card Holder Name',

                    'card_number' => 'Card Number',

                    'expiry' => 'Expiry',

                    'cvv' => 'CVV',

                    'single_installment' => 'Single Installment',

                    'installment' => 'Installment',

                    'new_card' => 'New Card',

                    'saved_card' => 'Saved Card',

                    'save_card' => 'Save Card',

                    '3D_payment' => '3D Payment',

                    'your_registered_cards' => 'Your Registered Cards',

                    'choose_card' => 'Choose Card',

                    'no_registered_card' => 'You Dont Have a Registered Card',

                    'delete_saved_card' => 'Delete',
                ],

                'EUR' => [
                    'card_holder_name' => 'Card Holder Name',

                    'card_number' => 'Card Number',

                    'expiry' => 'Expiry',

                    'cvv' => 'CVV',

                    'single_installment' => 'Single Installment',

                    'installment' => 'Installment',

                    'new_card' => 'New Card',

                    'saved_card' => 'Saved Card',

                    'save_card' => 'Save Card',

                    '3D_payment' => '3D Payment',

                    'your_registered_cards' => 'Your Registered Cards',

                    'choose_card' => 'Choose Card',

                    'no_registered_card' => 'You Dont Have a Registered Card',

                    'delete_saved_card' => 'Delete',
                ],
            ];

            if (!isset($lang[$language])) {
                $language = 'USD';
            }

            if (isset($lang[$language][$content])) {
                $localizeContent = $lang[$language][$content];
            } else {
                $localizeContent = $content;
            }

            return $localizeContent;
        }

        public function payment_fields()
        {

            if ($description = $this->get_description()) {
                echo wpautop(wptexturize($description));
            }

            $currency = get_option('woocommerce_currency');

            //app_secret

            $post = [
                'app_id' => $this->get_option('app_key'),

                'app_secret' => $this->get_option('app_secret'),
            ];

            $environment = $this->environment == "yes" ? 'TRUE' : 'FALSE';
            // Decide which URL to post to
            $environment_url = "FALSE" == $environment ? 'https://app.halkode.com.tr/ccpayment/api/token' : 'https://testapp.halkode.com.tr/ccpayment/api/token';

            $result = $this->curl($environment_url, 'POST', $post);

            if (is_wp_error($result)) {
                return;
            }


            if ($this->is_3d != 4 && $this->is_3d != 8) {
                echo '<fieldset id="wc-' . esc_attr($this->id) . '-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">';
            }

            if ($result->status_code == 100) {


                $this->is_3d = $result->data->is_3d;

                echo "<input type='hidden' name='halkode_token' class='halkode_token' id='halkode_token' value='" . $result->data->token . "'/>";

                if (!empty(WC()->cart->get_cart())) {
                    foreach (WC()->cart->get_cart() as $cart_item) {
                        $cart_product_id = $cart_item['product_id'];

                        $is_recurring_cart = get_post_meta($cart_product_id, "_recurring", true);

                        if ($is_recurring_cart == 'yes') {
                            $payment_duration = get_post_meta($cart_product_id, "payment_duration", true);

                            $payment_cycle = get_post_meta($cart_product_id, "payment_cycle", true);

                            $payment_interval = get_post_meta($cart_product_id, "payment_interval", true);

                            if (!empty($payment_duration) && !empty($payment_cycle) && !empty($payment_interval)) { ?>

                                <input class="recurring_checkbox" name="recurring_options[recurring_check]"
                                       type="hidden" value="yes">

                                <input type="hidden" name="recurring_options[payment_duration]" class="payment_duration"
                                       value="<?php echo $payment_duration; ?>"/>

                                <input type="hidden" name="recurring_options[payment_cycle]" class="payment_cycle"
                                       value="<?php echo $payment_cycle; ?>"/>

                                <input type="hidden" name="recurring_options[payment_interval]" class="payment_interval"
                                       value="<?php echo $payment_interval; ?>"/>

                            <?php }
                        }
                    }
                }
            } else {
                echo "<input type='hidden' name='halkode_token' class='halkode_token' id='halkode_token' value=''/>";
            }

            echo "<input type='hidden' name='halkode_3d' class='halkode_3d' id='halkode_3d' value='" . $this->is_3d . "'/>";

            if ($this->is_3d != 4 && $this->is_3d != 8) {
                // Add this action hook if you want your custom payment gateway to support it

                do_action('woocommerce_credit_card_form_start', $this->id);
                // I recommend to use inique IDs, because other gateways could already use #ccNo, #expdate, #cvc
                ?>
                <?php if (is_user_logged_in()): ?>
                    <p class="form-row form-row-wide">
                        <label>
                            <input type="radio" class="stored_card" checked name="stored_card"
                                   value="0"> <?php echo $this->getLocalizationContent('new_card', $currency); ?>
                        </label>
						<?php
						/*
                        <label>
                            <input type="radio" class="stored_card" name="stored_card"
                                   value="1"> <?php echo $this->getLocalizationContent('saved_card', $currency); ?>
                        </label>
						*/
						?>
                    </p>
                <?php endif ?>
                <div class="payment-form">
                    <p class="form-row form-row-wide">

                        <label><?php echo $this->getLocalizationContent('card_holder_name', $currency); ?> <span
                                    class="required">*</span></label>

                        <input id="cc_holder_name" name="cc_holder_name" class="input-text cc_holder_name alpha-only"
                               type="text" autocomplete="off">

                    </p>

                    <p class="form-row form-row-wide">

                        <label><?php echo $this->getLocalizationContent('card_number', $currency); ?> <span
                                    class="required">*</span></label>

                        <input id="cc_number" class="input-text cc_number" name="cc_number" type="number"
                               oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);"
                               maxlength="16" autocomplete="off">

                        <span class="halkode_spinner_blk"></span>

                    </p>

                    <p class="form-row form-row-first">

                        <label><?php echo $this->getLocalizationContent('expiry', $currency); ?> <span class="required">*</span></label>

                        <select name="expiry_month" id="expiry_month" class="input-select" style="padding:15px;">

                            <?php for ($i = 1; $i <= 12; $i++) {
                                echo '<option value="' . $i . '" >' . $i . '</option>';
                            } ?>

                        </select>

                        <select name="expiry_year" id="expiry_year" class="input-select" style="padding:15px;">

                            <?php for ($i = date('Y'); $i <= date('Y') + 10; $i++) {
                                echo '<option value="' . $i . '" >' . $i . '</option>';
                            } ?>

                        </select>

                    </p>

                    <p class="form-row form-row-last">

                        <label><?php echo $this->getLocalizationContent('cvv', $currency); ?> <span
                                    class="required">*</span></label>

                        <input id="cc_cvv" class="input-text cc_cvv" name="cc_cvv" type="password" maxlength="4"
                               autocomplete="off" placeholder="CVV">

                    </p>


                    <input type="hidden" name="pos_id" class="pos_id" value=""/>

                    <input type="hidden" name="pos_amount" class="pos_amount" value=""/>

                    <input type="hidden" name="currency_id" class="currency_id" value=""/>

                    <input type="hidden" name="campaign_id" class="campaign_id" value=""/>

                    <input type="hidden" name="currency_code" class="currency_code" value=""/>

                    <input type="hidden" name="allocation_id" class="allocation_id" value=""/>

                    <input type="hidden" name="installments_number" class="installments_number" value=""/>

                    <input type="hidden" name="hash_key" class="hash_key" value=""/>

                    <div class="clear"></div>

                    <?php
                        $instllment = $this->get_option('installment_enabled');

                        $dis = '';

                        if ($instllment == 'yes') {
                            $dis = "style='display:none'";
                        }
                    ?>

                    <p class="installments form-row form-row-wide" id="installments" <?php echo $dis; ?>></p>
                    <div class="clear"></div>

					<?php
					/*
						<p class="form-row form-row-wide" style="margin-top:10px;">
							<?php if (is_user_logged_in()): ?>
							<input style="width:auto;" id="save_card" class="save_card" name="save_card" type="checkbox"
								   autocomplete="off"
								   value="yes"><strong><?php echo $this->getLocalizationContent('save_card', $currency); ?></strong>

						</p>

                        <?php endif; ?>
					*/
					?>
					
                </div>
                <div class="clear"></div>
                <?php if (is_user_logged_in()): ?>


                    <div class="saved_card" style="display:none">
                        <div class="form-group">
                            <label class="col-sm-2 control-label"
                                   for="input-card-choice"><?php echo $this->getLocalizationContent('your_registered_cards', $currency); ?></label>
                            <div class="col-sm-8">
                                <?php global $wpdb;
                                    try {
                                        global $wpdb;


                                        $table_name = $wpdb->prefix . 'halkode_cards';
                                        $cards = $wpdb->get_results("SELECT * FROM $table_name where customer_id=" . get_current_user_id());

                                    } catch (\Exception $e) {
                                        error_log($e->getMessage());
                                    }
                                ?>
                                <select name="card_choice" style="width:100%" id="input-card-choice"
                                        class="input-text" <?php if (count($cards) == 0): ?> disabled <?php endif; ?> >


                                    <?php if (count($cards) > 0): ?>

                                        <option value=""><?php echo $this->getLocalizationContent('choose_card', $currency); ?></option>
                                        <?php foreach ($cards as $card): ?>
                                            <option value="<?php echo $card->card_token; ?>"><?php echo $card->card_mask; ?></option>
                                        <?php endforeach; ?>

                                    <?php else: ?>
                                        <option value=""><?php echo $this->getLocalizationContent('no_registered_card', $currency); ?></option>
                                    <?php endif; ?>


                                </select>
                            </div>
                            <div class="col-sm-2">
                                <input type="button"
                                       value="<?php echo $this->getLocalizationContent('delete_saved_card', $currency); ?>"
                                       id="button-delete"
                                       data-loading-text="<?php echo $this->getLocalizationContent('delete_saved_card', $currency); ?>"
                                       class="action danger checkout"/>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($this->is_3d == 1) { ?>

                    <p class="form-row form-row-wide">

                        <input style="width:auto;" id="pay_via_3d" class="pay_via_3d" name="pay_via_3d" type="checkbox"
                               autocomplete="off"
                               value="yes"><strong><?php echo $this->getLocalizationContent('3D_payment', $currency); ?></strong>

                    </p>

                <?php } ?>

                <?php
                /*<div class="recurring_block">

                <p class="form-row form-row-wide">

                    <input style="width:auto;" id="recurring_checkbox" class="recurring_checkbox" name="recurring_options[recurring_check]" type="checkbox" autocomplete="off" value="yes"> <strong><?php echo __('Recurring Payment', 'wc-gateway-offline')?></strong>

                </p>

                <div class="recurring_option_fields" style="display:none;">

                    <p class="form-row form-row-wide">

                        <label>No of Payments <span class="required">*</span></label>

                        <input type="number" name="recurring_options[payment_duration]" class="payment_duration" value=""/>

                    </p>

                    <p class="form-row form-row-wide">

                        <label>Order Frequency Cycle <span class="required">*</span></label>

                        <select name="recurring_options[payment_cycle]" class="payment_cycle">

                            <option value="D">Daily</option>

                            <option value="W">Weekly</option>

                            <option value="M">Monthly</option>

                            <option value="Y">Yearly</option>

                        </select>

                    </p>

                    <p class="form-row form-row-wide">

                        <label>Order Frequency Interval <span class="required">*</span></label>

                        <input type="number" name="recurring_options[payment_interval]" class="payment_interval" value=""/>

                    </p>

                </div>

            </div> */
                ?>

                <?php
                do_action('woocommerce_credit_card_form_end', $this->id);


                echo '<div class="clear"></div></fieldset>';

            }
        }


        public function payment_scripts()
        {


            if (!is_cart() && !is_checkout() && !isset($_GET['pay_for_order'])) {

                return;

            }
            if ('no' === $this->enabled) {

                return;

            }

            wp_register_script('woocommerce_halkode', plugins_url('js/halkode.js', __FILE__));

            wp_localize_script('woocommerce_halkode', 'halkode_var', array('spinner' => plugins_url('images/spinner.gif', __FILE__)));

            wp_enqueue_script('woocommerce_halkode');

            wp_enqueue_style('woocommerce_halkode_style', plugins_url('css/halkode.css', __FILE__));

        }

        public function curl($url, $method, $array, $header = [])
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


        // Response handled for payment gateway
        public function process_payment($order_id)
        {

            global $woocommerce;

            global $wp_session;
            $order = new WC_Order($order_id);




            // checking for transiction
            $environment = $this->environment == "yes" ? 'TRUE' : 'FALSE';
            // Decide which URL to post to
            if (isset($_POST['stored_card']) && $_POST['stored_card'] == 1) {
                $environment_url = "FALSE" == $environment ? 'https://app.halkode.com.tr/ccpayment/api/payByCardToken' : 'https://testapp.halkode.com.tr/ccpayment/api/payByCardToken';
            } else {
                if (isset($_POST['pay_via_3d']) && $_POST['pay_via_3d'] == 'yes') {
                    $environment_url = "FALSE" == $environment ? 'https://app.halkode.com.tr/ccpayment/api/paySmart3D' : 'https://testapp.halkode.com.tr/ccpayment/api/paySmart3D';
                } elseif (isset($_POST['halkode_3d']) && $_POST['halkode_3d'] == 2) {
                    $environment_url = "FALSE" == $environment ? 'https://app.halkode.com.tr/ccpayment/api/paySmart3D' : 'https://testapp.halkode.com.tr/ccpayment/api/paySmart3D';
                } else {
                    $environment_url = "FALSE" == $environment ? 'https://app.halkode.com.tr/ccpayment/api/paySmart2D' : 'https://testapp.halkode.com.tr/ccpayment/api/paySmart2D';
                }
            }


            // This is where the fun stuff begins
            $price = 0;
            /* Login for deduct discount amount */
            $dis_per_product_amount = 0;
            $dis_total_amount = 0;
            if ($order->get_discount_total() > 0) {
                $dis_total_amount = number_format($order->get_discount_total(), 2, ".", "");
                $item_count = count($order->get_items());
                $dis_per_product_amount = $dis_total_amount / $item_count;
            }

            $order_items = $order->get_items(array('line_item', 'fee', 'shipping'));
            foreach (WC()->cart->get_cart() as $cart_item) {
                $cart_product_id = $cart_item['product_id'];
                $is_recurring_cart = get_post_meta($cart_product_id, "_recurring", true);
            }

            foreach ($order_items as $item_id => $order_item) {


                $invoice['items'][] = [
                    'name' => $order_item->get_name(),

                    'price' => number_format($order_item->get_total(), 2, ".", "") / $order_item->get_quantity(),

                    'qty' => $order_item->get_quantity(),

                    'description' => '',
                ];

                $price = $price + (number_format(($order_item->get_total() - $dis_per_product_amount), 2, ".", ""));
            }

            if ($order->get_total_tax() > 0) {
                $invoice['items'][] = [
                    'name' => 'Tax',

                    'price' => number_format($order->get_total_tax(), 2, ".", ""),

                    'qty' => 1,

                    'description' => '',
                ];

                $price = $price + number_format($order->get_total_tax(), 2, ".", "");
            }

            $invoice['total'] = number_format($price, 2, ".", "");

            //BIlling info Optional

            $invoice['bill_address1'] = isset($_POST['billing_address_1']) ? $_POST['billing_address_1'] : '';

            $invoice['bill_address2'] = isset($_POST['billing_address_2']) ? $_POST['billing_address_2'] : '';

            $invoice['bill_city'] = isset($_POST['billing_city']) ? $_POST['billing_city'] : '';

            $invoice['bill_postcode'] = isset($_POST['billing_postcode']) ? $_POST['billing_postcode'] : '';

            $invoice['bill_state'] = isset($_POST['billing_state']) ? $_POST['billing_state'] : '';

            $invoice['bill_country'] = isset($_POST['billing_country']) ? $_POST['billing_country'] : '';

            $invoice['bill_email'] = isset($_POST['billing_email']) ? $_POST['billing_email'] : '';

            $invoice['bill_phone'] = isset($_POST['billing_phone']) ? $_POST['billing_phone'] : '';


            $return_url = $order->get_checkout_order_received_url();

            $date = explode(' / ', $_POST['halkode_sanalpos-card-expiry']);
            $month = $date[0];
            $year = strlen($date[1]) == 2 ? 20 . $date[1] : $date[1];

            $order = md5(microtime()) . 'WOO' . $order_id;

            if (isset($_POST['save_card']) && $_POST['save_card'] == 'yes') {

                global $wpdb;

                $getToken = $this->getToken();
                $customer_number = get_current_user_id();
                $api_secret = $this->get_option('app_secret');
                $api_key = $this->get_option('app_key');
                $merchant_key = $this->get_option('merchant_key');
                $merchant_id = $this->get_option('merchant_id');
                $sandbox = $this->get_option('environment');
                $hash = $this->generateSaveCardCreateHashKey(
                    $merchant_key,
                    $customer_number,
                    $_POST['cc_number'],
                    $_POST['cc_holder_name'],
                    $_POST['expiry_month'],
                    $_POST['expiry_year'],
                    $api_secret
                );


                $url = $sandbox == 'yes' ? 'https://testapp.halkode.com.tr/ccpayment/api/saveCard' : 'https://app.halkode.com.tr/ccpayment/api/saveCard';


                $array = [
                    'merchant_key' => $merchant_key,
                    'card_holder_name' => $_POST['cc_holder_name'],
                    'card_number' => $_POST['cc_number'],
                    'expiry_month' => $_POST['expiry_month'],
                    'expiry_year' => $_POST['expiry_year'],
                    'customer_number' => (string)$customer_number,
                    'hash_key' => $hash,

                ];


                $header[] = 'Content-type: application/json';
                $header[] = 'Authorization: Bearer ' . $getToken->data->token;


                $save_card = $this->curl($url, 'POST', json_encode($array), $header);
                $customer = $customer_number;

                $customer = (int)$customer;
                $cc_number = str_replace(' ', '', $_POST['cc_number']);
                $first_six = substr($cc_number, 0, 6);

                $last_for = substr($cc_number, 12, 4);

                $cc = $first_six . "******" . $last_for;


                if ($save_card->status_code == "100") {

                    $array = [
                        'customer_id' => $customer,
                        'card_token' => $save_card->card_token,
                        'card_mask' => $cc
                    ];
                    try {
                        $wpdb->insert($wpdb->prefix . 'halkode_cards', $array);
                    } catch (\Exception $e) {
                        error_log($e->getMessage());
                    }

                }

            }

            $installment = $_POST['installments_number'] >= 1 ? $_POST['installments_number'] : 1;

            $pay_data = [

                'cc_holder_name' => $_POST['cc_holder_name'],
                'cc_no' => str_replace(array(' ', '-'), '', $_POST['cc_number']),
                'cvv' => $_POST['cc_cvv'],
                'expiry_month' => $_POST['expiry_month'],
                'expiry_year' => $_POST['expiry_year'],
                'sale_web_hook_key' => $this->get_option('sale_webhook_key'),
                'currency_code' => get_option('woocommerce_currency'),
                'installments_number' => $installment,
                'invoice_id' => $order,
                'is_3d' => isset($_POST['pay_via_3d']) ? 'yes' : 'no',
                'is_2d_card' => $_POST['stored_card'] == 1 ? 'yes' : 'no',
                'token' => $_POST['halkode_token'],
                'invoice_description' => $order_id . " ödemesi",
                'transaction_type' => $this->get_option('transaction_type'),
                'total' => number_format(WC()->cart->total, 2, ".", ""),
                'merchant_key' => $this->get_option('merchant_key'),
                'items' => json_encode($invoice['items']),
                'name' => $_POST['billing_first_name'],
                'surname' => $_POST['billing_last_name'],
                'bill_address1' => $_POST['billing_address_1'] ?? '',
                'bill_address2' => $_POST['billing_address_2'] ?? '',
                'bill_city' => $_POST['billing_city'] ?? '',
                'bill_postcode' => $_POST['billing_postcode'] ?? '',
                'bill_state' => $_POST['billing_state'] ?? '',
                'bill_country' => $_POST['billing_country'] ?? '',
                'bill_email' => $_POST['billing_email'] ?? '',
                'bill_phone' => $_POST['billing_phone'] ?? '',
                'hash_key' => $this->generateHashKey(number_format(WC()->cart->total, 2, ".", ""), $installment, get_option('woocommerce_currency'), $this->get_option('merchant_key'), $order, $this->get_option('app_secret')),
                'return_url' => $return_url,
                'cancel_url' => wc_get_checkout_url(),


            ];

            if (isset($_POST['stored_card']) && $_POST['stored_card'] == 1) {
                $pay_data['customer_number'] = get_current_user_id();
                $pay_data['customer_email'] = $_POST['billing_email'];
                $pay_data['customer_phone'] = '123456789';
                $pay_data['customer_name'] = $_POST['billing_first_name'] . ' ' . $_POST['billing_last_name'];
                $pay_data['card_token'] = $_POST['card_choice'];
                unset($pay_data['cc_holder_name']);
                unset($pay_data['cc_no']);
                unset($pay_data['card_owner']);
                unset($pay_data['expiry_month']);
                unset($pay_data['expiry_year']);
                unset($pay_data['cvv']);
                unset($pay_data['card_save']);
            }

            if (isset($_POST['halkode_3d']) && ($_POST['halkode_3d'] == 4 || $_POST['halkode_3d'] == 8)) {
                $environment_url = "FALSE" == $environment ? 'https://app.halkode.com.tr/ccpayment/purchase/link' : 'https://testapp.halkode.com.tr/ccpayment/purchase/link';
                unset($pay_data['cc_holder_name']);
                unset($pay_data['cc_no']);
                unset($pay_data['card_owner']);
                unset($pay_data['expiry_month']);
                unset($pay_data['expiry_year']);
                unset($pay_data['cvv']);
                unset($pay_data['card_save']);
            }

            if (isset($_POST['pay_via_3d'])) {

            } elseif (isset($_POST['halkode_3d']) && ($_POST['halkode_3d'] == 2)) {

            } else {
                unset($pay_data['items']);
                $pay_data['items'] = $invoice['items'];
            }


            if ($is_recurring_cart == 'yes') {
                $pay_data['recurring_web_hook_key'] = $this->get_option('recurring_sale_webhook_key');
                $pay_data['order_type'] = "1";
                $pay_data['recurring_payment_number'] = get_post_meta($cart_product_id, 'payment_duration', true);
                $pay_data['recurring_payment_cycle'] = get_post_meta($cart_product_id, 'payment_cycle', true);
                $pay_data['recurring_payment_interval'] = get_post_meta($cart_product_id, 'payment_interval', true);
            }


            $form = "<form id='halkode-form' action='" . $environment_url . "' method='POST'>";

            foreach ($pay_data as $key => $item) {

                $form .= "<input type='hidden' name='{$key}' value='" . $item . "'>";
            }


            //$form .= '<form>';
            $form .= '<form><script>document.getElementById("halkode-form").submit()</script>';
            //
            if (isset($_POST['pay_via_3d'])) {
                update_post_meta($order_id, 'halkode_payment_form', base64_encode(serialize($form)));
            } elseif (isset($_POST['halkode_3d']) && ($_POST['halkode_3d'] == 2)) {
                update_post_meta($order_id, 'halkode_payment_form', base64_encode(serialize($form)));
            } elseif (isset($_POST['halkode_3d']) && ($_POST['halkode_3d'] == 4 || $_POST['halkode_3d'] == 8)) {
                $pay_data['purchase'] = 'yes';
                unset($pay_data['is_2d_card']);

            update_post_meta($order_id, 'halkode_payment_form', base64_encode(serialize($pay_data)));
            } else {

            update_post_meta($order_id, 'halkode_payment_form', base64_encode(serialize($pay_data)));
            }

            return array(

                'result' => 'success',

                'redirect' => get_site_url() . '/?order_id=' . $order_id

            );


        }

        function get_admin_installment()
        {

            $inst = [];

            $post = [
                'app_id' => $this->get_option('app_key'),

                'app_secret' => $this->get_option('app_secret'),
            ];


            $environment = $this->get_option('environment') == "yes" ? 'TRUE' : 'FALSE';

            // Decide which URL to post to
            $environment_url = "FALSE" == $environment ? 'https://app.halkode.com.tr/ccpayment/api/token' : 'https://testapp.halkode.com.tr/ccpayment/api/token';


            if (!empty($this->get_option('app_key')) && !empty($this->get_option('app_secret')) && !empty($this->get_option('merchant_key')) && !empty($this->get_option('environment'))) {

                $token = getCurl($environment_url, 'POST', $post);

                $token = $token->data->token;
                $environment_url = "FALSE" == $environment ? 'https://app.halkode.com.tr/ccpayment/api/installments' : 'https://testapp.halkode.com.tr/ccpayment/api/installments';
                $headers = ['Accept: application/json', 'Content-Type: application/json', "Authorization: Bearer $token"];
                $a['merchant_key'] = $this->get_option('merchant_key');

                $installments = getCurl($environment_url, 'POST', json_encode($a), $headers);
                //error_log(json_encode($installments));
                $inst = [];

                foreach ($installments->installments as $key => $installment) {
                    $inst[$key + 1] = $installment;
                }


                return $inst;


            }

            return $inst;
        }

        public function generateHashKey($total, $installment, $currency_code, $merchant_key, $invoice_id,
                                        $app_secret)
        {

            $data = $total . '|' . $installment . '|' . $currency_code . '|' . $merchant_key . '|' . $invoice_id;

            $iv = substr(sha1(mt_rand()), 0, 16);
            $password = sha1($app_secret);

            $salt = substr(sha1(mt_rand()), 0, 4);
            $saltWithPassword = hash('sha256', $password . $salt);

            $encrypted = openssl_encrypt("$data", 'aes-256-cbc', "$saltWithPassword", null, $iv);

            $msg_encrypted_bundle = "$iv:$salt:$encrypted";
            $msg_encrypted_bundle = str_replace('/', '__', $msg_encrypted_bundle);

            return $msg_encrypted_bundle;
        }

        public function generateSaveCardCreateHashKey(
            $merchant_key,
            $customer_number,
            $card_number,
            $card_holder_name,
            $expiry_month,
            $expiry_year,
            $app_secret
        )
        {
            $data = $merchant_key . '|' . $customer_number . '|' . $card_holder_name . '|' . $card_number . '|' . $expiry_month . '|' . $expiry_year;
            $iv = substr(sha1(mt_rand()), 0, 16);
            $password = sha1($app_secret);
            $salt = substr(sha1(mt_rand()), 0, 4);
            $saltWithPassword = hash('sha256', $password . $salt);
            $encrypted = openssl_encrypt("$data", 'aes-256-cbc', "$saltWithPassword", null, $iv);
            $msg_encrypted_bundle = "$iv:$salt:$encrypted";
            $msg_encrypted_bundle = str_replace('/', '__', $msg_encrypted_bundle);

            return $msg_encrypted_bundle;

        }

        private function getToken()
        {

            $api_secret = $this->get_option('app_secret');
            $api_key = $this->get_option('app_key');
            $merchant_key = $this->get_option('merchant_key');
            $merchant_id = $this->get_option('merchant_id');
            $sandbox = $this->get_option('environment');


            $url = $sandbox == 'yes' ? 'https://testapp.halkode.com.tr/ccpayment/api/token' : 'https://app.halkode.com.tr/ccpayment/api/token';

            $array = [
                'app_id' => $api_key,
                'app_secret' => $api_secret
            ];

            return $this->curl($url, 'POST', $array);

        }

        // Validate fields
        public function validate_fields()
        {
            if (isset($_POST['stored_card']) && $_POST['stored_card'] == 1) {
                if (isset($_POST['card_choice']) && empty($_POST['card_choice'])) {
                    wc_add_notice('Kayıtlı kart seçmek zorunludur!', 'error');
                }
            }
            if (isset($_POST['stored_card']) && $_POST['stored_card'] != 1) {
                if (isset($_POST['cc_holder_name']) && empty($_POST['cc_holder_name'])) {
                    wc_add_notice('Kart sahibi alanı zorunludur!', 'error');

                    return false;
                }

                if (isset($_POST['cc_number']) && empty($_POST['cc_number'])) {
                    wc_add_notice('Kart numarası alanı zorunludur!', 'error');

                    return false;
                }


                if (isset($_POST['expiry_month']) && empty($_POST['expiry_month'])) {
                    wc_add_notice('Kart son kullanım ayı zorunludur!', 'error');

                    return false;
                }
                if (isset($_POST['expiry_year']) && empty($_POST['expiry_year'])) {
                    wc_add_notice('Kart son kullanım yılı zorunludur!', 'error');

                    return false;
                }
                if (isset($_POST['cc_cvv']) && empty($_POST['cc_cvv'])) {
                    wc_add_notice('Kart cvv zorunludur!', 'error');

                    return false;
                }
            }


        }

        public function do_ssl_check()
        {
            if ($this->enabled == "yes") {
                if (get_option('woocommerce_force_ssl_checkout') == "no") {
                    echo "<div class=\"error\"><p>" .
                        sprintf(
                            __(
                                "<strong>%s</strong> is enabled and WooCommerce is not forcing the SSL certificate on your checkout page. Please ensure that you have a valid SSL certificate and that you are <a href=\"%s\">forcing the checkout pages to be secured.</a>"
                            ),
                            $this->method_title,
                            admin_url('admin.php?page=wc-settings&tab=checkout')
                        ) .
                        "</p></div>";
                }
            }
        }
    }
