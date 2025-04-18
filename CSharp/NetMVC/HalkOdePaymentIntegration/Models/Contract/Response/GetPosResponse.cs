using System.Collections.Generic;

namespace HalkOdePaymentIntegration.Contract.Response
{
    public class GetPosResponse
    {
        public int status_code { get; set; }
        public string status_description { get; set; }
        public List<Details> data { get; set; } = new List<Details>();
    }

    public class Details
    {
        public int pos_id { get; set; }
        public int campaign_id { get; set; }
        public int allocation_id { get; set; }
        public double installments_number { get; set; }
        public string card_type { get; set; }
        public string card_program { get; set; }
        public string card_scheme { get; set; }
        public double payable_amount { get; set; }
        public string hash_key { get; set; }
        public string amount_to_be_paid { get; set; }
        public string currency_code { get; set; }
        public int currency_id { get; set; }
        public string title { get; set; }
 
    }
}