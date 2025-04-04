namespace HalkOdePaymentIntegration.Contract.Response
{

    public class CommissionResponse
    {
        public int status_code { get; set; }
        public string? status_description { get; set; }
        public List<Detail> data { get; set; } = new List<Detail>();
    }


    public class Detail
    {
        public string? title { get; set; }
        public string? card_program { get; set; }
        public int? merchant_commission_percentage { get; set; }
        public int? merchant_commission_fixed { get; set; }
        public int? user_commission_percentage { get; set; }
        public int? user_commission_fixed { get; set; }
        public string? currency_code { get; set; }
        public string? installment { get; set; }
        public int? pos_id { get; set; }
      
    }
}