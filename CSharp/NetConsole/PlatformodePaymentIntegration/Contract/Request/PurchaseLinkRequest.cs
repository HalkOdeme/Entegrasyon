namespace PlatformodePaymentIntegration.Contract.Request;

public class PurchaseLinkRequest
{
    public string merchant_key { get; set; }
    public string currency_code { get; set; }
    public string invoice { get; set; }
    public string name { get; set; }
    public string surname { get; set; }
}
