using PlatformodePaymentIntegration.Contract.Request;
using PlatformodePaymentIntegration.Contract.Response;
using PlatformodePaymentIntegration.Extension;
using PlatformodePaymentIntegration.Generate;
using PlatformodePaymentIntegration.Settings;
using System.Text.Json;

namespace PlatformodePaymentIntegration;

public class GetTransactionApi
{
    private const string URL = "api/getTransactions";

    private readonly HttpClient _httpClient;
    private readonly ApiSettings _apiSettings;

    public GetTransactionApi()
    {
        _httpClient = new HttpClient();
        _apiSettings = new ApiSettingConfiguration().Configuration();
    }

    private async Task<string?> GetAsync(string date)
    {
        var tokenResponse = await new TokenApi().GetAsync();

        GetTransactionRequest getTransactionRequest = CreateRequestParameter(_apiSettings, date);

        var jsonRequest = JsonSerializer.Serialize(getTransactionRequest);

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

    private GetTransactionRequest CreateRequestParameter(ApiSettings apiSettings, string date)
    {
        GetTransactionRequest getTransactionRequest = new()
        {
            date = date,
            invoice_id = string.Empty,
            currency_id = string.Empty,
            merchant_key = apiSettings.MerchantKey
        };

        HashGenerator hashGenerator = new();

        getTransactionRequest.hash_key = hashGenerator.GenerateHashKey(
            true,
            getTransactionRequest.date ?? "",
            getTransactionRequest.invoice_id ?? "",
            getTransactionRequest.currency_id ?? "",
            getTransactionRequest.payment_method_id ?? "",
            getTransactionRequest.minamount.ToString()?.Replace(",", ".") ?? "",
            getTransactionRequest.maxamount.ToString()?.Replace(",", ".") ?? "",
            getTransactionRequest.transactionState ?? ""
            );

        return getTransactionRequest;
    }

    public async Task PrintAsync(string date)
    {
        GetTransactionRequest getTransactionRequest = CreateRequestParameter(_apiSettings, date);

        var response = await GetAsync(date);

        Console.WriteLine();
        ConsoleExtensions.BoxedOutput("Endpoint Bilgileri");
        ConsoleExtensions.WriteLineWithSubTitle("Endpoint Adresi : ", _apiSettings.BaseAddress + URL);
        ConsoleExtensions.WriteLineWithSubTitle("Method Tipi : ", HttpMethod.Post);
        ConsoleExtensions.BoxedOutput("Request Parametreler");
        ConsoleExtensions.WriteLineWithSubTitle($"merchant_key : ", getTransactionRequest.merchant_key);
        ConsoleExtensions.WriteLineWithSubTitle($"hash_key : ", getTransactionRequest.hash_key);
        ConsoleExtensions.WriteLineWithSubTitle($"date : ", getTransactionRequest.date);
        ConsoleExtensions.WriteLineWithSubTitle($"invoice_id : ", getTransactionRequest.invoice_id);
        ConsoleExtensions.WriteLineWithSubTitle($"currency_id : ", getTransactionRequest.currency_id);
        ConsoleExtensions.WriteLineWithSubTitle($"minamount : ", getTransactionRequest.minamount);
        ConsoleExtensions.WriteLineWithSubTitle($"maxamount : ", getTransactionRequest.maxamount);
        ConsoleExtensions.WriteLineWithSubTitle($"transactionState : ", getTransactionRequest.transactionState);
        ConsoleExtensions.WriteLineWithSubTitle($"payment_method_id : ", getTransactionRequest.payment_method_id);
        ConsoleExtensions.BoxedOutput("Response Parametreler");
        ConsoleExtensions.WriteLineWithSubTitle("response : ", response ?? "Response alınamadı.");
    }
}