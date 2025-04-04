using PlatformodePaymentIntegration.Contract.Request;
using PlatformodePaymentIntegration.Contract.Response;
using PlatformodePaymentIntegration.Extension;
using PlatformodePaymentIntegration.Settings;
using System.Text.Json;

namespace PlatformodePaymentIntegration;

public class InstallmentApi
{
    private const string URL = "api/installments";

    private readonly HttpClient _httpClient;
    private readonly ApiSettings _apiSettings;

    public InstallmentApi()
    {
        _httpClient = new HttpClient();
        _apiSettings = new ApiSettingConfiguration().Configuration();
    }

    private async Task<InstallmentResponse?> GetAsync()
    {
        var tokenResponse = await new TokenApi().GetAsync();

        InstallmentRequest installmentRequest = CreateRequestParameter(_apiSettings);

        var jsonRequest = JsonSerializer.Serialize(installmentRequest);

        var httpContent = new StringContent(jsonRequest, System.Text.Encoding.UTF8, "application/json");

        _httpClient.DefaultRequestHeaders.Authorization = new System.Net.Http.Headers.AuthenticationHeaderValue("Bearer", tokenResponse?.data?.token);

        try
        {
            var httpResponse = await _httpClient.PostAsync($"{_apiSettings.BaseAddress}{URL}", httpContent);

            var jsonResponse = await httpResponse.Content.ReadAsStringAsync();

            return JsonSerializer.Deserialize<InstallmentResponse>(jsonResponse);
        }
        catch (Exception ex)
        {
            var message = $"Beklenmedik bir hata oluştu. Lütfen gönderdiğiniz parametreleri kontrol ediniz ya da sistem yöneticisine başvurunuz.({ex.Message})";
            ConsoleExtensions.BoxedOutputForErrorMessage("", message);
            throw;
        }
    }

    private InstallmentRequest CreateRequestParameter(ApiSettings apiSettings)
    {
        InstallmentRequest installmentRequest = new()
        {
            merchant_key = apiSettings.MerchantKey
        };

        return installmentRequest;
    }

    public async Task PrintAsync()
    {
        InstallmentRequest installmentRequest = CreateRequestParameter(_apiSettings);

        var response = await GetAsync();

        Console.WriteLine();
        ConsoleExtensions.BoxedOutput("Endpoint Bilgileri");
        ConsoleExtensions.WriteLineWithSubTitle("Endpoint Adresi : ", _apiSettings.BaseAddress + URL);
        ConsoleExtensions.WriteLineWithSubTitle("Method Tipi : ", HttpMethod.Post);
        ConsoleExtensions.BoxedOutput("Request Parametreler");
        ConsoleExtensions.WriteLineWithSubTitle($"merchant_key : ", installmentRequest.merchant_key);
        ConsoleExtensions.BoxedOutput("Response Parametreler");
        if (response != null)
        {
            ConsoleExtensions.WriteLineWithSubTitle($"status_code : ", response.status_code);
            ConsoleExtensions.WriteLineWithSubTitle($"status_description : ", response.message);
            ConsoleExtensions.WriteLineWithSubTitle("installments : ", JsonSerializer.Serialize(response.installments));
            Console.WriteLine();
        }
        else
        {
            Console.WriteLine("Response alınamadı.");
        }
    }
}
