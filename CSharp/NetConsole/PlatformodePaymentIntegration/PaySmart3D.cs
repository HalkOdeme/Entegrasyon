using PlatformodePaymentIntegration.Contract.Request;
using PlatformodePaymentIntegration.Contract.Response;
using PlatformodePaymentIntegration.Extension;
using PlatformodePaymentIntegration.Generate;
using PlatformodePaymentIntegration.Settings;
using System.Text.Json;

namespace PlatformodePaymentIntegration;

public class PaySmart3D
{
    private const string URL = "api/paySmart3D";

    private readonly HttpClient _httpClient;
    private readonly ApiSettings _apiSettings;

    public PaySmart3D()
    {
        _httpClient = new HttpClient();
        _apiSettings = new ApiSettingConfiguration().Configuration();
    }

    private async Task<string?> GetAsync()
    {
        var tokenResponse = await new TokenApi().GetAsync();

        PaySmart3DRequest paySmart3DRequest = CreateRequestParameter(_apiSettings);

        var jsonRequest = JsonSerializer.Serialize(paySmart3DRequest);

        var httpContent = new StringContent(jsonRequest, System.Text.Encoding.UTF8, "application/json");

        _httpClient.DefaultRequestHeaders.Authorization = new System.Net.Http.Headers.AuthenticationHeaderValue("Bearer", tokenResponse?.data?.token);

        try
        {
            var httpResponse = await _httpClient.PostAsync($"{_apiSettings.BaseAddress}{URL}", httpContent);

            return await httpResponse.Content.ReadAsStringAsync();
        }
        catch (Exception ex)
        {
            var message = $"Beklenmedik bir hata oluştu. Lütfen gönderdiğiniz parametreleri kontrol ediniz ya da sistem yöneticisine başvurunuz.({ex.Message})";
            ConsoleExtensions.BoxedOutputForErrorMessage("", message);
            throw;
        }
    }

    private PaySmart3DRequest CreateRequestParameter(ApiSettings apiSettings)
    {
        PaySmart3DRequest paySmart3DRequest = new()
        {
            cc_holder_name = "Test kart",
            cc_no = "4132260000000003",
            expiry_month = "11",
            expiry_year = "2027",
            currency_code = "TRY",
            installments_number = 1,
            invoice_id = InvoiceGenerator.GenerateInvoiceId(),
            invoice_description = "INVOICE TEST DESCRIPTION",
            total = 10,
            items = new List<Item3D>
            {
                new Item3D()
                {
                    name = "item",
                    price = 10,
                    quantity = 1,
                    description = "item description"
                }
            },
            name = "John",
            surname = "Dao",
            return_url = "https://www.google.com/",
            cancel_url = "https://www.github.com/",
            payment_completed_by = "app",    // optional "merchant" tanımlanırsa /payment/complete apisi çağrılarak ödeme tamamlanır.       
            cvv = "555",  // optional
            merchant_key = apiSettings.MerchantKey
        };

        HashGenerator hashGenerator = new HashGenerator();

        paySmart3DRequest.hash_key = hashGenerator.GenerateHashKey(
            false,
            paySmart3DRequest.total.ToString()?.Replace(",", ".") ?? "",
            paySmart3DRequest.installments_number.ToString(),
            paySmart3DRequest.currency_code,
            paySmart3DRequest.merchant_key,
            paySmart3DRequest.invoice_id);

        return paySmart3DRequest;
    }

    public async Task PrintAsync()
    {
        PaySmart3DRequest paySmart3DRequest = CreateRequestParameter(_apiSettings);

        var response = await GetAsync();

        Console.WriteLine();
        ConsoleExtensions.BoxedOutput("Endpoint Bilgileri");
        ConsoleExtensions.WriteLineWithSubTitle("Endpoint Adresi : ", _apiSettings.BaseAddress + URL);
        ConsoleExtensions.WriteLineWithSubTitle("Method Tipi : ", HttpMethod.Post);
        ConsoleExtensions.BoxedOutput("Request Parametreler");
        ConsoleExtensions.WriteLineWithSubTitle($"cc_holder_name : ", paySmart3DRequest.cc_holder_name);
        ConsoleExtensions.WriteLineWithSubTitle($"cc_no : ", paySmart3DRequest.cc_no);
        ConsoleExtensions.WriteLineWithSubTitle($"expiry_month : ", paySmart3DRequest.expiry_month);
        ConsoleExtensions.WriteLineWithSubTitle($"expiry_year : ", paySmart3DRequest.expiry_year);
        ConsoleExtensions.WriteLineWithSubTitle($"currency_code : ", paySmart3DRequest.currency_code);
        ConsoleExtensions.WriteLineWithSubTitle($"installments_number : ", paySmart3DRequest.installments_number);
        ConsoleExtensions.WriteLineWithSubTitle($"invoice_id : ", paySmart3DRequest.invoice_id);
        ConsoleExtensions.WriteLineWithSubTitle($"invoice_description : ", paySmart3DRequest.invoice_description);
        ConsoleExtensions.WriteLineWithSubTitle($"total : ", paySmart3DRequest.total);
        ConsoleExtensions.WriteLineWithSubTitle($"items : ",JsonSerializer.Serialize(paySmart3DRequest.items));
        ConsoleExtensions.WriteLineWithSubTitle($"name : ", paySmart3DRequest.name);
        ConsoleExtensions.WriteLineWithSubTitle($"surname : ", paySmart3DRequest.surname);
        ConsoleExtensions.WriteLineWithSubTitle($"return_url : ", paySmart3DRequest.return_url);
        ConsoleExtensions.WriteLineWithSubTitle($"cancel_url : ", paySmart3DRequest.cancel_url);
        ConsoleExtensions.WriteLineWithSubTitle($"payment_completed_by : ", paySmart3DRequest.payment_completed_by);  // Optional
        ConsoleExtensions.WriteLineWithSubTitle($"cvv : ", paySmart3DRequest.cvv);    // Optional
        ConsoleExtensions.WriteLineWithSubTitle($"merchant_key : ", paySmart3DRequest.merchant_key);
        ConsoleExtensions.WriteLineWithSubTitle($"hash_key : ", paySmart3DRequest.hash_key);
        ConsoleExtensions.BoxedOutput("Response Parametreler");
        ConsoleExtensions.WriteLineWithSubTitle("response : ",response ?? "Response alınamadı.");
    }


    public void PrintHtml()
    {
        PaySmart3DRequest paySmart3DRequest = CreateRequestParameter(_apiSettings);

        Console.WriteLine();
        ConsoleExtensions.BoxedOutput("3D ile test edebilmek için 3dtest.html dosyası oluşturup aşağıdaki kodları yapıştırıp çalıştırınız.");

        string htmlCode = $@"<html>
<title>3D Test with Cvv</title>
<body>
<form name=""form"" action=""{_apiSettings.BaseAddress + URL}"" method=""POST"">
    <input type=""hidden"" name=""cc_holder_name"" value=""{paySmart3DRequest.cc_holder_name}"">
    <input type=""hidden"" name=""cc_no"" value=""{paySmart3DRequest.cc_no}"">
    <input type=""hidden"" name=""expiry_month"" value=""{paySmart3DRequest.expiry_month}"">
    <input type=""hidden"" name=""expiry_year"" value=""{paySmart3DRequest.expiry_year}"">
    <input type=""hidden"" name=""currency_code"" value=""{paySmart3DRequest.currency_code}"">
    <input type=""hidden"" name=""installments_number"" value=""{paySmart3DRequest.installments_number}"">
    <input type=""hidden"" name=""invoice_id"" value=""{paySmart3DRequest.invoice_id}"">
    <input type=""hidden"" name=""invoice_description"" value=""{paySmart3DRequest.invoice_description}"">
    <input type=""hidden"" name=""total"" value=""{paySmart3DRequest.total}"">
    <input type=""hidden"" name=""items"" value='{JsonSerializer.Serialize(paySmart3DRequest.items)}'>
    <input type=""hidden"" name=""name"" value=""{paySmart3DRequest.name}"">
    <input type=""hidden"" name=""surname"" value=""{paySmart3DRequest.surname}"">
    <input type=""hidden"" name=""return_url"" value=""{paySmart3DRequest.return_url}"">
    <input type=""hidden"" name=""cancel_url"" value=""{paySmart3DRequest.cancel_url}"">
    <input type=""hidden"" name=""payment_completed_by"" value=""{paySmart3DRequest.payment_completed_by}"">
    <input type=""hidden"" name=""cvv"" value=""{paySmart3DRequest.cvv}"">
    <input type=""hidden"" name=""merchant_key"" value=""{paySmart3DRequest.merchant_key}"">
    <input type=""hidden"" name=""hash_key"" value=""{paySmart3DRequest.hash_key}"">
</form> 
</body> 
<script type=""text/javascript"">
var form = document.forms[0];
form.submit();
</script>
</html>";

        Console.WriteLine(htmlCode);
    }
}
