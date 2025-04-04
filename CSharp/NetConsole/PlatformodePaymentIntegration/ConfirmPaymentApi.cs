using PlatformodePaymentIntegration.Contract.Request;
using PlatformodePaymentIntegration.Contract.Response;
using PlatformodePaymentIntegration.Extension;
using PlatformodePaymentIntegration.Settings;
using PlatformodePaymentIntegration.Generate;
using System.Text.Json;

namespace PlatformodePaymentIntegration;

public class ConfirmPaymentApi
{
    private const string URL = "api/confirmPayment";

    private readonly HttpClient _httpClient;
    private readonly ApiSettings _apiSettings;

    public ConfirmPaymentApi()
    {
        _httpClient = new HttpClient();
        _apiSettings = new ApiSettingConfiguration().Configuration();
    }

    private async Task<ConfirmPaymentResponse?> GetAsync(string invoice_id)
    {
            var tokenResponse = await new TokenApi().GetAsync();

            ConfirmPaymentRequest confirmPaymentRequest = CreateRequestParameter(_apiSettings, invoice_id);

            var jsonRequest = JsonSerializer.Serialize(confirmPaymentRequest);

            var httpContent = new StringContent(jsonRequest, System.Text.Encoding.UTF8, "application/json");

            _httpClient.DefaultRequestHeaders.Authorization = new System.Net.Http.Headers.AuthenticationHeaderValue("Bearer", tokenResponse?.data?.token);

        try
        {
            var httpResponse = await _httpClient.PostAsync($"{_apiSettings.BaseAddress}{URL}", httpContent);

            var jsonResponse = await httpResponse.Content.ReadAsStringAsync();

            return JsonSerializer.Deserialize<ConfirmPaymentResponse>(jsonResponse);
        }
        catch (Exception ex)
        {
            var message = $"Beklenmedik bir hata oluştu. Lütfen gönderdiğiniz parametreleri kontrol ediniz ya da sistem yöneticisine başvurunuz.({ex.Message})";
            ConsoleExtensions.BoxedOutputForErrorMessage("", message);
            throw;
        }
    }

    private ConfirmPaymentRequest CreateRequestParameter(ApiSettings apiSettings, string invoice_id)
    {
        ConfirmPaymentRequest confirmPaymentRequest = new()
        {
            total = 10,
            invoice_id = invoice_id,
            status = "1",
            merchant_key = apiSettings.MerchantKey
        };

        HashGenerator hashGenerator = new();

        confirmPaymentRequest.hash_key = hashGenerator.GenerateHashKey(
            false,
           confirmPaymentRequest.merchant_key,
            confirmPaymentRequest.invoice_id,
            confirmPaymentRequest.status);

        return confirmPaymentRequest;
    }

    public async Task PrintAsync(string invoice_id)
    {
        ConfirmPaymentRequest confirmPaymentRequest = CreateRequestParameter(_apiSettings, invoice_id);

        var response = await GetAsync(invoice_id);

        Console.WriteLine();
        ConsoleExtensions.BoxedOutput("Endpoint Bilgileri");
        ConsoleExtensions.WriteLineWithSubTitle("Endpoint Adresi : ", _apiSettings.BaseAddress + URL);
        ConsoleExtensions.WriteLineWithSubTitle("Method Tipi : ", HttpMethod.Post);
        ConsoleExtensions.BoxedOutput("Request Parametreler");
        ConsoleExtensions.WriteLineWithSubTitle("total : ", confirmPaymentRequest.total);
        ConsoleExtensions.WriteLineWithSubTitle("invoice_id : ", confirmPaymentRequest.invoice_id);
        ConsoleExtensions.WriteLineWithSubTitle("status : ", confirmPaymentRequest.status);
        ConsoleExtensions.WriteLineWithSubTitle("merchant_key : ", confirmPaymentRequest.merchant_key);
        ConsoleExtensions.WriteLineWithSubTitle("hash_key : ", confirmPaymentRequest.hash_key);
        ConsoleExtensions.BoxedOutput("Response Parametreler");
        if (response != null)
        {
            ConsoleExtensions.WriteLineWithSubTitle("status_code : ", response.status_code);
            ConsoleExtensions.WriteLineWithSubTitle("status_description : ", response.status_description);
            ConsoleExtensions.WriteLineWithSubTitle("transaction_status : ", response.transaction_status);
            ConsoleExtensions.WriteLineWithSubTitle("order_id : ", response.order_id);
            ConsoleExtensions.WriteLineWithSubTitle("invoice_id : ", response.invoice_id);
        }
        else
        {
            Console.WriteLine("Response alınamadı.");
        }
    }
}