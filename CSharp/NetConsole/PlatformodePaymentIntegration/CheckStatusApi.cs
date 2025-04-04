using PlatformodePaymentIntegration.Contract.Request;
using PlatformodePaymentIntegration.Contract.Response;
using PlatformodePaymentIntegration.Extension;
using PlatformodePaymentIntegration.Settings;
using System.Text.Json;

namespace PlatformodePaymentIntegration;

public class CheckStatusApi
{
    private const string URL = "api/checkstatus";

    private readonly HttpClient _httpClient;
    private readonly ApiSettings _apiSettings;

    public CheckStatusApi()
    {
        _httpClient = new HttpClient();
        _apiSettings = new ApiSettingConfiguration().Configuration();
    }

    private async Task<CheckStatusResponse?> GetAsync(string invoice_id)
    {
        var tokenResponse = await new TokenApi().GetAsync();

        CheckStatusRequest checkStatusRequest = CreateRequestParameter(_apiSettings, invoice_id);

        var jsonRequest = JsonSerializer.Serialize(checkStatusRequest);

        var httpContent = new StringContent(jsonRequest, System.Text.Encoding.UTF8, "application/json");

        _httpClient.DefaultRequestHeaders.Authorization = new System.Net.Http.Headers.AuthenticationHeaderValue("Bearer", tokenResponse?.data?.token);

        try
        {
            var httpResponse = await _httpClient.PostAsync($"{_apiSettings.BaseAddress}{URL}", httpContent);

            var jsonResponse = await httpResponse.Content.ReadAsStringAsync();

            return JsonSerializer.Deserialize<CheckStatusResponse>(jsonResponse);
        }
        catch (Exception ex)
        {
            var message = $"Beklenmedik bir hata oluştu. Lütfen gönderdiğiniz parametreleri kontrol ediniz ya da sistem yöneticisine başvurunuz.({ex.Message})";
            ConsoleExtensions.BoxedOutputForErrorMessage("", message);
            throw;
        }
    }

    private CheckStatusRequest CreateRequestParameter(ApiSettings apiSettings, string invoice_id)
    {
        CheckStatusRequest checkStatusRequest = new()
        {
            invoice_id = invoice_id,
            merchant_key = apiSettings.MerchantKey
        };

        return checkStatusRequest;
    }

    public async Task PrintAsync(string invoice_id)
    {
        CheckStatusRequest checkStatusRequest = CreateRequestParameter(_apiSettings, invoice_id);

        var response = await GetAsync(invoice_id);

        Console.WriteLine();
        ConsoleExtensions.BoxedOutput("Endpoint Bilgileri");
        ConsoleExtensions.WriteLineWithSubTitle("Endpoint Adresi : ", _apiSettings.BaseAddress + URL);
        ConsoleExtensions.WriteLineWithSubTitle("Method Tipi : ", HttpMethod.Post);
        ConsoleExtensions.BoxedOutput("Request Parametreler");
        ConsoleExtensions.WriteLineWithSubTitle($"invoice_id : ", checkStatusRequest.invoice_id);
        ConsoleExtensions.WriteLineWithSubTitle($"merchant_key : ", checkStatusRequest.merchant_key);
        ConsoleExtensions.BoxedOutput("Response Parametreler");
        if (response != null)
        {
            ConsoleExtensions.WriteLineWithSubTitle($"status_code : ", response.status_code);
            ConsoleExtensions.WriteLineWithSubTitle($"status_description : ", response.status_description);
            ConsoleExtensions.WriteLineWithSubTitle($"transaction_status : ", response.transaction_status);
            ConsoleExtensions.WriteLineWithSubTitle($"order_id : ", response.order_id);
            ConsoleExtensions.WriteLineWithSubTitle($"transaction_id : ", response.transaction_id);
            ConsoleExtensions.WriteLineWithSubTitle($"message : ", response.message);
            ConsoleExtensions.WriteLineWithSubTitle($"reason : ", response.reason);
            ConsoleExtensions.WriteLineWithSubTitle($"bank_status_code : ", response.bank_status_code);
            ConsoleExtensions.WriteLineWithSubTitle($"bank_status_description : ", response.bank_status_description);
            ConsoleExtensions.WriteLineWithSubTitle($"invoice_id : ", response.invoice_id);
            ConsoleExtensions.WriteLineWithSubTitle($"total_refunded_amount : ", response.total_refunded_amount);
            ConsoleExtensions.WriteLineWithSubTitle($"product_price : ", response.product_price);
            ConsoleExtensions.WriteLineWithSubTitle($"transaction_amount : ", response.transaction_amount);
            ConsoleExtensions.WriteLineWithSubTitle($"ref_number : ", response.ref_number);
            ConsoleExtensions.WriteLineWithSubTitle($"transaction_type : ", response.transaction_type);
            ConsoleExtensions.WriteLineWithSubTitle($"original_bank_error_code : ", response.original_bank_error_code);
            ConsoleExtensions.WriteLineWithSubTitle($"original_bank_error_description : ", response.original_bank_error_description);
            ConsoleExtensions.WriteLineWithSubTitle($"merchant_commission : ", response.merchant_commission);
            ConsoleExtensions.WriteLineWithSubTitle($"user_commission : ", response.user_commission);
            ConsoleExtensions.WriteLineWithSubTitle($"settlement_date : ", response.settlement_date);
        }
        else
        {
            Console.WriteLine("Response alınamadı.");
        }
    }
}