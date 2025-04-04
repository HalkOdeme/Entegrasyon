namespace PlatformodePaymentIntegration.Contract.Response;

public class CheckStatusResponse
{
    public int status_code { get; set; }
    public string status_description { get; set; }
    public string transaction_status { get; set; }
    public string order_id { get; set; }
    public string transaction_id { get; set; }
    public string message { get; set; }
    public string reason { get; set; }
    public string bank_status_code { get; set; }
    public string bank_status_description { get; set; }
    public string invoice_id { get; set; }
    public double total_refunded_amount { get; set; }
    public string product_price { get; set; }
    public double transaction_amount { get; set; }
    public string ref_number { get; set; }
    public string transaction_type { get; set; }
    public string original_bank_error_code { get; set; }
    public string original_bank_error_description { get; set; }
    public string merchant_commission { get; set; }
    public string user_commission { get; set; }
    public string settlement_date { get; set; }
}
