namespace PlatformodePaymentIntegration.Contract.Response;

public class InstallmentResponse
{
    public int status_code { get; set; }
    public string message { get; set; }
    public List<int> installments { get; set; }
}