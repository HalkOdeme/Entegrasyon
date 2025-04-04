namespace PlatformodePaymentIntegration.Contract.Request;

public class ConfirmPaymentRequest
{
    public double total { get; set; }
    public string invoice_id { get; set; }
    public string status { get; set; }
    public string merchant_key { get; set; }
    public string hash_key { get; set; }
}
