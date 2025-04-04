namespace PlatformodePaymentIntegration.Contract.Request;

public class PaySmart3DRequest
{
    public string cc_holder_name { get; set; }
    public string cc_no { get; set; }
    public string expiry_month { get; set; }
    public string expiry_year { get; set; }
    public string currency_code { get; set; }
    public int installments_number { get; set; }
    public string invoice_id { get; set; }
    public string invoice_description { get; set; }
    public double total { get; set; }
    public List<Item3D> items { get; set; } = new List<Item3D>();
    public string name { get; set; }
    public string surname { get; set; }
    public string return_url { get; set; }
    public string cancel_url { get; set; }
    public string payment_completed_by { get; set; }    // optional
    public string cvv { get; set; } // optional
    public string merchant_key { get; set; }
    public string hash_key { get; set; }
}


public class Item3D
{
    public string name { get; set; }
    public int price { get; set; }
    public int quantity { get; set; }
    public string description { get; set; }
}