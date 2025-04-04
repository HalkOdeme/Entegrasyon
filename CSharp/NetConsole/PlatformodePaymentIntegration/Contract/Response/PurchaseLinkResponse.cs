namespace PlatformodePaymentIntegration.Contract.Response;

public class PurchaseLinkResponse
{
    public bool status { get; set; }
    public int status_code { get; set; }
    public string success_message { get; set; }
    public string link { get; set; }
    public string order_id { get; set; }
}