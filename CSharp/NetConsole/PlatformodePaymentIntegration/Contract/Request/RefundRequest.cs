namespace PlatformodePaymentIntegration.Contract.Request;

public class RefundRequest
{
    public string invoice_id { get; set; }
    public double amount { get; set; }
    public string app_id { get; set; }
    public string app_secret { get; set; }
    public string merchant_key { get; set; }
    public string hash_key { get; set; }
}
