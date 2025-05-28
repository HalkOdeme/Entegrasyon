namespace HalkOdePaymentIntegration.Contract.Response
{
    public class PaySmart3DResponse
    {
        public int status_code { get; set; }
        public string status_description { get; set; }
        public Datas Data { get; set; }
    }


    public class Datas
    {
        public string order_no { get; set; }
        public string order_id { get; set; }
        public string invoice_id { get; set; }
        public string credit_card_no { get; set; }
        public string transaction_type { get; set; }
        public string payment_completed_by { get; set; }
        public int? payment_status { get; set; }
        public int? payment_method { get; set; }
        public object error_code { get; set; }
        public string error { get; set; }
        public object auth_code { get; set; }
        public int? merchant_commission { get; set; }
        public int? user_commission { get; set; }
        public int? merchant_commission_percentage { get; set; }
        public int? merchant_commission_fixed { get; set; }
        public string hash_key { get; set; }
        public object original_bank_error_code { get; set; }
        public object original_bank_error_description { get; set; }

        public string is_commission_from_user { get; set; }
        public string commission_by { get; set; }
    }
}