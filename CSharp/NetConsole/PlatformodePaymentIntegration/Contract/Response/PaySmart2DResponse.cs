namespace PlatformodePaymentIntegration.Contract.Response;

public class PaySmart2DResponse
{
    public int status_code { get; set; }
    public string status_description { get; set; }
    public Data data { get; set; }
}


public class Data
{
    public string order_no { get; set; }
    public string order_id { get; set; }
    public string invoice_id { get; set; }
    public string credit_card_no { get; set; }
    public string transaction_type { get; set; }
    public int? payment_status { get; set; }
    public int? payment_method { get; set; }
    public object? error_code { get; set; }
    public string error { get; set; }
    public object? auth_code { get; set; }
    public int? merchant_commission { get; set; }
    public int? user_commission { get; set; }
    public int? merchant_commission_percentage { get; set; }
    public int? merchant_commission_fixed { get; set; }
    public string hash_key { get; set; }
    public object? original_bank_error_code { get; set; }
    public object? original_bank_error_description { get; set; }
}