namespace PlatformodePaymentIntegration.Contract.Response;

public class CompleteResponse
{
    public int status_code { get; set; }
    public string status_description { get; set; }
    public CompleteData data { get; set; }
}

public class CompleteData
{
    public string invoice_id { get; set; }
    public string order_id { get; set; }
}