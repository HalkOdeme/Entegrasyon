namespace PlatformodePaymentIntegration.Contract.Request;

public class CheckStatusRequest
{
    public string invoice_id { get; set; }
    public string merchant_key { get; set; }
}
