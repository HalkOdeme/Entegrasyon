using HalkOdePaymentIntegration.Contract.Response;

namespace HalkOdePaymentIntegration.Models.Contract.Response
{
    public class TaksitResponse
    {
       
            public int status_code { get; set; }
            public string? message { get; set; }
            public string installments { get; set; }



    }
}
