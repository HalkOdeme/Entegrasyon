namespace PlatformodePaymentIntegration.Contract.Request;

public class GetTransactionRequest
{
    public string merchant_key { get; set; }
    public string hash_key { get; set; }
    public string date { get; set; }
    public string? invoice_id { get; set; }  // optional
    public string? currency_id { get; set; } // optional
    public string? payment_method_id { get; set; } // optional
    public double? minamount { get; set; } // optional
    public double? maxamount { get; set; } // optional
    public string? transactionState { get; set; } // optional
}