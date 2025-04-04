namespace PlatformodePaymentIntegration.Contract.Response;

public class ConfirmPaymentResponse
{
    public int status_code { get; set; }
    public string status_description { get; set; }
    public string transaction_status { get; set; }
    public string order_id { get; set; }
    public string invoice_id { get; set; }
}
