namespace PlatformodePaymentIntegration.Contract.Response;

public class RefundResponse
{
    public int status_code { get; set; }
    public string status_description { get; set; }
    public string order_no { get; set; }
    public string invoice_id { get; set; }
    public string ref_no { get; set; }
    public string ref_number { get; set; }
}