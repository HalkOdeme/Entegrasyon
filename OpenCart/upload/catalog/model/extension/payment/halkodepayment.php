<?php 
class ModelExtensionPaymenthalkodepayment extends Model {
	public function recurringPayments(){
        return 1;
    }
	public function getMethod($address, $total) {
		$this->language->load('extension/payment/halkodepayment');
		
		$status = 1;
		$method_data = array();

		if ($status) {  
			$method_data = array(
				'code'       => 'halkodepayment',
				'title'      => $this->language->get('text_title'),
				'terms'      => '',
				'sort_order' => $this->config->get('halkodepayment_sort_order')
			);
		}

		return $method_data;
	}
		public function appendi($transaction_data) {

		$this->db->query("INSERT INTO `" . DB_PREFIX . "halkode_transactions` SET 
			`order_id` = '" . (int)$transaction_data['order_id'] . "',
			`status` = '" . $this->db->escape($transaction_data['status']) . "',
			`amount` = '" . $this->db->escape($transaction_data['amount']) . "',
			`currency` = '" . $this->db->escape($transaction_data['currency']) . "',
			`type` = '" . $this->db->escape($transaction_data['type']) . "',
			`reference` = '" . $this->db->escape($transaction_data['reference']) . "',
			`operation` = '" . $this->db->escape($transaction_data['operation']) . "',
			`transactionId` = '" . $this->db->escape($transaction_data['transactionId']) . "',
			`message` = '" . $this->db->escape($transaction_data['message']) . "',
			`code` = '" . $this->db->escape($transaction_data['code']) . "',
			`purchase_url` = '" . $this->db->escape($transaction_data['purchase_url']). "'"
		);

		return $this->db->getLastId();
	}
}
?>