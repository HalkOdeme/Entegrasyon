<?php
class ModelExtensionPaymenthalkodepayment extends Model
{
    public function recurringPayments(){
        return 1;
    }
    public function install()
    {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "halkode_transactions` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `order_id` int(11) NOT NULL,
              `status` varchar(255) NOT NULL,
              `amount` varchar(255) DEFAULT NULL,
              `currency` varchar(255) DEFAULT NULL,
              `type` varchar(255) DEFAULT NULL,
              `reference` varchar(255) DEFAULT NULL,
              `operation` varchar(255) DEFAULT NULL,
              `transactionId` varchar(255) DEFAULT NULL,
              `message` varchar(255) DEFAULT NULL,
              `code` varchar(255) DEFAULT NULL,
              `purchase_url` text,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
        ");
    }

    public function uninstall()
    {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "halkode_transactions`");
    }

    public function appendi($transaction_data)
    {

        $this->db->query("INSERT INTO `" . DB_PREFIX . "halkode_transactions` SET
            `order_id` = '" . (int) $transaction_data['order_id'] . "',
            `status` = '" . $this->db->escape($transaction_data['status']) . "',
            `amount` = '" . $this->db->escape($transaction_data['amount']) . "',
            `currency` = '" . $this->db->escape($transaction_data['currency']) . "',
            `type` = '" . $this->db->escape($transaction_data['type']) . "',
            `reference` = '" . $this->db->escape($transaction_data['reference']) . "',
            `operation` = '" . $this->db->escape($transaction_data['operation']) . "',
            `transactionId` = '" . $this->db->escape($transaction_data['transactionId']) . "',
            `message` = '" . $this->db->escape($transaction_data['message']) . "',
            `code` = '" . $this->db->escape($transaction_data['code']) . "',
            `purchase_url` = '" . $this->db->escape($transaction_data['purchase_url'])
        );

        return $this->db->getLastId();
    }

    public function updateTransaction($transaction)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "halkode_transactions SET halkodepayment_order_id = " . (int) $transaction['halkodepayment_order_id'] . ", transaction_id = '" . $this->db->escape($transaction['transaction_id']) . "', parent_id = '" . $this->db->escape($transaction['parent_id']) . "', date_added = '" . $this->db->escape($transaction['date_added']) . "', note = '" . $this->db->escape($transaction['note']) . "', msgsubid = '" . $this->db->escape($transaction['msgsubid']) . "', receipt_id = '" . $this->db->escape($transaction['receipt_id']) . "', payment_type = '" . $this->db->escape($transaction['payment_type']) . "', payment_status = '" . $this->db->escape($transaction['payment_status']) . "', pending_reason = '" . $this->db->escape($transaction['pending_reason']) . "', transaction_entity = '" . $this->db->escape($transaction['transaction_entity']) . "', amount = '" . $this->db->escape($transaction['amount']) . "', debug_data = '" . $this->db->escape($transaction['debug_data']) . "', call_data = '" . $this->db->escape($transaction['call_data']) . "' WHERE halkodepayment_order_transaction_id = '" . (int) $transaction['halkodepayment_order_transaction_id'] . "'");
    }

    public function getOrderReferenceNo($id_order)
    {
        return DB::getInstance()->executeS("select * from " . self::$definition["table"] . " where order_id = " . pSQL($id_order));
    }
    public static function getTransactionFromReferenceNo($referenceNo)
    {
        $result = DB::getInstance()->executeS("select id from " . self::$definition["table"] . " where reference = " . pSQL($referenceNo));
        return new Transaction($result["id"]);
    }

    public function gethalkodepaymentOrder($order_id)
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "halkodepayment_order` WHERE `order_id` = '" . (int) $order_id . "'");

        return $query->row;
    }

    public function edithalkodepaymentOrderStatus($order_id, $capture_status)
    {
        $this->db->query("UPDATE `" . DB_PREFIX . "halkodepayment_order` SET `capture_status` = '" . $this->db->escape($capture_status) . "', `date_modified` = NOW() WHERE `order_id` = '" . (int) $order_id . "'");
    }

    public function gethalkodepaymentOrderByTransactionId($transaction_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "halkodepayment_order_transaction WHERE transaction_id = '" . $this->db->escape($transaction_id) . "'");

        return $query->rows;
    }

    public function getFailedTransaction($halkodepayment_order_transaction_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "halkodepayment_order_transaction WHERE halkodepayment_order_transaction_id = '" . (int) $halkodepayment_order_transaction_id . "'");

        return $query->row;
    }

    public function getLocalTransaction($transaction_id)
    {
        $result = $this->db->query("SELECT * FROM " . DB_PREFIX . "halkodepayment_order_transaction WHERE transaction_id = '" . $this->db->escape($transaction_id) . "'")->row;

        if ($result) {
            return $result;
        } else {
            return false;
        }
    }

    public function getTransaction($transaction_id)
    {
        $call_data = array(
            'METHOD'        => 'GetTransactionDetails',
            'TRANSACTIONID' => $transaction_id,
        );

        return $this->call($call_data);
    }

    public function getCurrencies()
    {
        return array(
            'AUD',
            'BRL',
            'CAD',
            'CZK',
            'DKK',
            'EUR',
            'HKD',
            'HUF',
            'ILS',
            'JPY',
            'MYR',
            'MXN',
            'NOK',
            'NZD',
            'PHP',
            'PLN',
            'GBP',
            'SGD',
            'SEK',
            'CHF',
            'TWD',
            'THB',
            'TRY',
            'USD',
        );
    }

    public function getOrderId($transaction_id)
    {
        $query = $this->db->query("SELECT `o`.`order_id` FROM `" . DB_PREFIX . "halkodepayment_order_transaction` `ot` LEFT JOIN `" . DB_PREFIX . "halkodepayment_order` `o`  ON `o`.`halkodepayment_order_id` = `ot`.`halkodepayment_order_id`  WHERE `ot`.`transaction_id` = '" . $this->db->escape($transaction_id) . "' LIMIT 1");

        return $query->row['order_id'];
    }

    public function getCapturedTotal($halkodepayment_order_id)
    {
        $query = $this->db->query("SELECT SUM(`amount`) AS `amount` FROM `" . DB_PREFIX . "halkodepayment_order_transaction` WHERE `halkodepayment_order_id` = '" . (int) $halkodepayment_order_id . "' AND `pending_reason` != 'authorization' AND (`payment_status` = 'Partially-Refunded' OR `payment_status` = 'Completed' OR `payment_status` = 'Pending') AND `transaction_entity` = 'payment'");

        return $query->row['amount'];
    }

    public function getRefundedTotal($halkodepayment_order_id)
    {
        $query = $this->db->query("SELECT SUM(`amount`) AS `amount` FROM `" . DB_PREFIX . "halkodepayment_order_transaction` WHERE `halkodepayment_order_id` = '" . (int) $halkodepayment_order_id . "' AND `payment_status` = 'Refunded' AND `parent_id` != ''");

        return $query->row['amount'];
    }

    public function getRefundedTotalByParentId($transaction_id)
    {
        $query = $this->db->query("SELECT SUM(`amount`) AS `amount` FROM `" . DB_PREFIX . "halkodepayment_order_transaction` WHERE `parent_id` = '" . $this->db->escape($transaction_id) . "' AND `payment_type` = 'refund'");

        return $query->row['amount'];
    }

    public function cleanReturn($data)
    {
        $data = explode('&', $data);

        $arr = array();

        foreach ($data as $k => $v) {
            $tmp          = explode('=', $v);
            $arr[$tmp[0]] = urldecode($tmp[1]);
        }

        return $arr;
    }


    public function getOrder($order_id)
    {
        $qry = $this->db->query("SELECT * FROM `" . DB_PREFIX . "halkodepayment_order` WHERE `order_id` = '" . (int) $order_id . "' LIMIT 1");

        if ($qry->num_rows) {
            $order                 = $qry->row;
            $order['transactions'] = $this->getTransactions($order['halkodepayment_order_id']);
            $order['captured']     = $this->totalCaptured($order['halkodepayment_order_id']);
            return $order;
        } else {
            return false;
        }
    }

    public function totalCaptured($halkodepayment_order_id)
    {
        $qry = $this->db->query("SELECT SUM(`amount`) AS `amount` FROM `" . DB_PREFIX . "halkodepayment_order_transaction` WHERE `halkodepayment_order_id` = '" . (int) $halkodepayment_order_id . "' AND `pending_reason` != 'authorization' AND (`payment_status` = 'Partially-Refunded' OR `payment_status` = 'Completed' OR `payment_status` = 'Pending') AND `transaction_entity` = 'payment'");

        return $qry->row['amount'];
    }

    public function getTransactions($halkodepayment_order_id)
    {
        $query = $this->db->query("SELECT `ot`.*, (SELECT COUNT(`ot2`.`halkodepayment_order_id`) FROM `" . DB_PREFIX . "halkodepayment_order_transaction` `ot2` WHERE `ot2`.`parent_id` = `ot`.`transaction_id`) AS `children` FROM `" . DB_PREFIX . "halkodepayment_order_transaction` `ot` WHERE `halkodepayment_order_id` = '" . (int) $halkodepayment_order_id . "' ORDER BY `date_added` ASC");

        return $query->rows;
    }




}
