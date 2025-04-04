using PlatformodePaymentIntegration.Contract.Request;
using PlatformodePaymentIntegration.Contract.Response;
using PlatformodePaymentIntegration.Extension;
using PlatformodePaymentIntegration.Generate;
using PlatformodePaymentIntegration.Settings;
using System.Text.Json;

namespace PlatformodePaymentIntegration;

public class RefundApi
{
    private const string URL = "api/refund";

    private readonly HttpClient _httpClient;
    private readonly ApiSettings _apiSettings;

    public RefundApi()
    {
        _httpClient = new HttpClient();
        _apiSettings = new ApiSettingConfiguration().Configuration();
    }

    private async Task<RefundResponse?> GetAsync(string invoice_id)
    {
        var tokenResponse = await new TokenApi().GetAsync();

        RefundRequest refundRequest = CreateRequestParameter(_apiSettings, invoice_id);
        
        var jsonRequest = JsonSerializer.Serialize(refundRequest);

        var httpContent = new StringContent(jsonRequest, System.Text.Encoding.UTF8, "application/json");

        _httpClient.DefaultRequestHeaders.Authorization = new System.Net.Http.Headers.AuthenticationHeaderValue("Bearer", tokenResponse?.data?.token);

        try
        {
            var httpResponse = await _httpClient.PostAsync($"{_apiSettings.BaseAddress}{URL}", httpContent);

            var jsonResponse = await httpResponse.Content.ReadAsStringAsync();

            return JsonSerializer.Deserialize<RefundResponse>(jsonResponse);
        }
        catch (Exception ex)
        {
            var message = $"Beklenmedik bir hata oluştu. Lütfen gönderdiğiniz parametreleri kontrol ediniz ya da sistem yöneticisine başvurunuz.({ex.Message})";
            ConsoleExtensions.BoxedOutputForErrorMessage("", message);
            throw;
        }
    }

    private RefundRequest CreateRequestParameter(ApiSettings apiSettings, string invoice_id)
    {
        RefundRequest refundRequest = new()
        {
            invoice_id = invoice_id,
            amount = 10,
            app_id = apiSettings.AppId,
            app_secret = apiSettings.AppSecret,
            merchant_key = apiSettings.MerchantKey
        };

        HashGenerator hashGenerator = new();

        refundRequest.hash_key = hashGenerator.GenerateHashKey(
            false,
            refundRequest.amount.ToString()?.Replace(",", ".") ?? "",
            refundRequest.invoice_id,
            refundRequest.merchant_key);

        return refundRequest;
    }

    public async Task PrintAsync(string invoice_id)
    {
        RefundRequest refundRequest = CreateRequestParameter(_apiSettings, invoice_id);

        var response = await GetAsync(invoice_id);

        Console.WriteLine();
        ConsoleExtensions.BoxedOutput("Endpoint Bilgileri");
        ConsoleExtensions.WriteLineWithSubTitle("Endpoint Adresi : ", _apiSettings.BaseAddress + URL);
        ConsoleExtensions.WriteLineWithSubTitle("Method Tipi : ", HttpMethod.Post);
        ConsoleExtensions.BoxedOutput("Request Parametreler");
        ConsoleExtensions.WriteLineWithSubTitle("invoice_id : ",refundRequest.invoice_id);
        ConsoleExtensions.WriteLineWithSubTitle("amount : ", refundRequest.amount);
        ConsoleExtensions.WriteLineWithSubTitle("app_id : ", refundRequest.app_id);
        ConsoleExtensions.WriteLineWithSubTitle("app_secret : ", refundRequest.app_secret);
        ConsoleExtensions.WriteLineWithSubTitle("merchant_key : ", refundRequest.merchant_key);
        ConsoleExtensions.WriteLineWithSubTitle("hash_key : ", refundRequest.hash_key);
        ConsoleExtensions.BoxedOutput("Response Parametreler");
        if (response != null)
        {
            ConsoleExtensions.WriteLineWithSubTitle("status_code : ", response.status_code);
            ConsoleExtensions.WriteLineWithSubTitle("status_description : ", response.status_description);
            ConsoleExtensions.WriteLineWithSubTitle("order_no : ", response.order_no);
            ConsoleExtensions.WriteLineWithSubTitle("invoice_id : ", response.invoice_id);
            ConsoleExtensions.WriteLineWithSubTitle("ref_no : ", response.ref_no);
            ConsoleExtensions.WriteLineWithSubTitle("ref_number : ", response.ref_number);
        }
        else
        {
            Console.WriteLine("Response alınamadı.");
        }
    }
}
