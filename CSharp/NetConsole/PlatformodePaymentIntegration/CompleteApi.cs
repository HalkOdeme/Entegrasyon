using PlatformodePaymentIntegration.Contract.Request;
using PlatformodePaymentIntegration.Contract.Response;
using PlatformodePaymentIntegration.Extension;
using PlatformodePaymentIntegration.Generate;
using PlatformodePaymentIntegration.Settings;
using System.Text.Json;

namespace PlatformodePaymentIntegration;

public class CompleteApi
{
    private const string URL = "payment/complete";

    private readonly HttpClient _httpClient;
    private readonly ApiSettings _apiSettings;

    public CompleteApi()
    {
        _httpClient = new HttpClient();
        _apiSettings = new ApiSettingConfiguration().Configuration();
    }

    private async Task<CompleteResponse?> GetAsync(string invoice_id, string order_id, string status)
    {
        var tokenResponse = await new TokenApi().GetAsync();

        CompleteRequest completeRequest = CreateRequestParameter(_apiSettings, invoice_id, order_id, status);

        var jsonRequest = JsonSerializer.Serialize(completeRequest);

        var httpContent = new StringContent(jsonRequest, System.Text.Encoding.UTF8, "application/json");

        _httpClient.DefaultRequestHeaders.Authorization = new System.Net.Http.Headers.AuthenticationHeaderValue("Bearer", tokenResponse?.data?.token);

        try
        {
            var httpResponse = await _httpClient.PostAsync($"{_apiSettings.BaseAddress}{URL}", httpContent);

            var jsonResponse = await httpResponse.Content.ReadAsStringAsync();

            return JsonSerializer.Deserialize<CompleteResponse>(jsonResponse);
        }
        catch (Exception ex)
        {
            var message = $"Beklenmedik bir hata oluştu. Lütfen gönderdiğiniz parametreleri kontrol ediniz ya da sistem yöneticisine başvurunuz.({ex.Message})";
            ConsoleExtensions.BoxedOutputForErrorMessage("", message);
            throw;
        }
    }

    private CompleteRequest CreateRequestParameter(ApiSettings apiSettings, string invoice_id, string order_id, string status)
    {
        CompleteRequest completeRequest = new()
        {
            invoice_id = invoice_id,
            order_id = order_id,
            status = status,
            merchant_key = apiSettings.MerchantKey
        };

        HashGenerator hashGenerator = new();

        completeRequest.hash_key = hashGenerator.GenerateHashKey(
            false,
            completeRequest.merchant_key,
            completeRequest.invoice_id,
            completeRequest.order_id,
            completeRequest.status);

        return completeRequest;
    }

    public async Task PrintAsync(string invoice_id, string order_id, string status)
    {
        CompleteRequest completeRequest = CreateRequestParameter(_apiSettings, invoice_id, order_id, status);

        var response = await GetAsync(invoice_id, order_id, status);

        Console.WriteLine();
        ConsoleExtensions.BoxedOutput("Endpoint Bilgileri");
        ConsoleExtensions.WriteLineWithSubTitle("Endpoint Adresi : ", _apiSettings.BaseAddress + URL);
        ConsoleExtensions.WriteLineWithSubTitle("Method Tipi : ", HttpMethod.Post);
        ConsoleExtensions.BoxedOutput("Request Parametreler");
        ConsoleExtensions.WriteLineWithSubTitle("invoice_id : ", completeRequest.invoice_id);
        ConsoleExtensions.WriteLineWithSubTitle("order_id : ", completeRequest.order_id);
        ConsoleExtensions.WriteLineWithSubTitle("status : ", completeRequest.status);
        ConsoleExtensions.WriteLineWithSubTitle("merchant_key : ", completeRequest.merchant_key);
        ConsoleExtensions.WriteLineWithSubTitle("hash_key : ", completeRequest.hash_key);
        ConsoleExtensions.BoxedOutput("Response Parametreler");
        if (response != null)
        {
            ConsoleExtensions.WriteLineWithSubTitle("status_code : ", response.status_code);
            ConsoleExtensions.WriteLineWithSubTitle("status_description : ", response.status_description);
            ConsoleExtensions.WriteLineWithSubTitle("invoice_id : ",response.data?.invoice_id);
            ConsoleExtensions.WriteLineWithSubTitle("order_id : ", response.data?.order_id);
        }
        else
        {
            Console.WriteLine("Response alınamadı.");
        }
    }
}