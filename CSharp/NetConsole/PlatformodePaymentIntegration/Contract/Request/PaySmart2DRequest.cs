namespace PlatformodePaymentIntegration.Contract.Request;

public class PaySmart2DRequest
{
    public string cc_holder_name { get; set; }
    public string cc_no { get; set; }
    public string expiry_month { get; set; }
    public string expiry_year { get; set; }
    public string cvv { get; set; }
    public string currency_code { get; set; }
    public int installments_number { get; set; }
    public string invoice_id { get; set; }
    public string invoice_description { get; set; }
    public double total { get; set; }
    public List<Item2D> items { get; set; } = new List<Item2D>();
    public string name { get; set; }
    public string surname { get; set; }
    public string merchant_key { get; set; }
    public string hash_key { get; set; }
}

public class Item2D
{
    public string name { get; set; }
    public double price { get; set; }
    public int quantity { get; set; }
    public string description { get; set; }
}