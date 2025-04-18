namespace HalkOdePaymentIntegration.Contract.Request
{
    public class GetPosRequest
    {
        public string credit_card { get; set; }
        public string amount { get; set; }
        public string currency_code { get; set; }
        public string merchant_key { get; set; }
  
    }
}
