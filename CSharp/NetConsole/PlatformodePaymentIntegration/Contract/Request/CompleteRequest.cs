namespace PlatformodePaymentIntegration.Contract.Request;

public class CompleteRequest
{
    public string merchant_key { get; set; }
    public string hash_key { get; set; }
    public string invoice_id { get; set; }
    public string order_id { get; set; }
    public string status { get; set; }
}
