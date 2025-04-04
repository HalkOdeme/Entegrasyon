using PlatformodePaymentIntegration.Contract.Request;
using PlatformodePaymentIntegration.Contract.Response;
using PlatformodePaymentIntegration.Extension;
using PlatformodePaymentIntegration.Settings;
using System.Text.Json;

namespace PlatformodePaymentIntegration;

public class CommissionApi
{
    private const string URL = "api/commissions";

    private readonly HttpClient _httpClient;
    private readonly ApiSettings _apiSettings;

    public CommissionApi()
    {
        _httpClient = new HttpClient();
        _apiSettings = new ApiSettingConfiguration().Configuration();
    }

    private async Task<string?> GetAsync()
    {
        var tokenResponse = await new TokenApi().GetAsync();

        CommissionRequest commissionRequest = CreateRequestParameter();

        var jsonRequest = JsonSerializer.Serialize(commissionRequest);

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

    private CommissionRequest CreateRequestParameter()
    {
        CommissionRequest commissionRequest = new CommissionRequest();
        commissionRequest.currency_code = "TRY";

        return commissionRequest;
    }

    public async Task PrintAsync()
    {
        CommissionRequest commissionRequest = CreateRequestParameter();

        var response = await GetAsync();

        Console.WriteLine();
        ConsoleExtensions.BoxedOutput("Endpoint Bilgileri");
        ConsoleExtensions.WriteLineWithSubTitle("Endpoint Adresi : ", _apiSettings.BaseAddress + URL);
        ConsoleExtensions.WriteLineWithSubTitle("Method Tipi : ", HttpMethod.Post);
        ConsoleExtensions.BoxedOutput("Request Parametreler");
        ConsoleExtensions.WriteLineWithSubTitle($"currency_code : ", commissionRequest.currency_code);
        ConsoleExtensions.BoxedOutput("Response Parametreler");
        ConsoleExtensions.WriteLineWithSubTitle("status_code", $"{response ?? "Response alınamadı."}");
    }
}