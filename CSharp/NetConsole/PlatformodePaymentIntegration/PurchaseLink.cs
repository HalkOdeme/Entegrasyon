using PlatformodePaymentIntegration.Contract.Request;
using PlatformodePaymentIntegration.Contract.Response;
using PlatformodePaymentIntegration.Extension;
using PlatformodePaymentIntegration.Settings;
using System.Text.Json;

namespace PlatformodePaymentIntegration;

public class PurchaseLink
{
    private const string URL = "purchase/link";

    private readonly HttpClient _httpClient;
    private readonly ApiSettings _apiSettings;

    public PurchaseLink()
    {
        _httpClient = new HttpClient();
        _apiSettings = new ApiSettingConfiguration().Configuration();
    }

    private async Task<PurchaseLinkResponse?> GetAsync()
    {
        PurchaseLinkRequest purchaseLinkRequest = CreateRequestParameter(_apiSettings);

        var formData = new Dictionary<string, string>
            {
                { "merchant_key", purchaseLinkRequest.merchant_key },
                { "currency_code", purchaseLinkRequest.currency_code },
                { "invoice", purchaseLinkRequest.invoice },
                { "name", purchaseLinkRequest.name },
                { "surname", purchaseLinkRequest.surname },
            };

        var httpRequestMessage = new HttpRequestMessage(HttpMethod.Post, $"{_apiSettings.BaseAddress}{URL}")
        {
            Content = new FormUrlEncodedContent(formData)
        };

        try
        {
            var httpResponse = await _httpClient.SendAsync(httpRequestMessage);

            var response = await httpResponse.Content.ReadAsStringAsync();

            return JsonSerializer.Deserialize<PurchaseLinkResponse>(response);
        }
        catch (Exception ex)
        {
            var message = $"Beklenmedik bir hata oluştu. Lütfen gönderdiğiniz parametreleri kontrol ediniz ya da sistem yöneticisine başvurunuz.({ex.Message})";
            ConsoleExtensions.BoxedOutputForErrorMessage("", message);
            throw;
        }
    }

    private PurchaseLinkRequest CreateRequestParameter(ApiSettings apiSettings)
    {
        PurchaseLinkRequest purchaseLinkRequest = new()
        {
            merchant_key = apiSettings.MerchantKey,
            currency_code = "TRY",
            invoice = "{\"invoice_id\":\"example-12\",\"invoice_description\":\"Testdescription\",\"total\":5.00,\"return_url\":\"https://google.com.tr\",\"cancel_url\":\"https://github.com.tr\",\"items\":[{\"name\":\"Item1\",\"price\":5,\"quantity\":1,\"description\":\"Test\"}]}",
            name = "John",
            surname = "Dao"
        };

        return purchaseLinkRequest;
    }

    public async Task PrintAsync()
    {
        PurchaseLinkRequest purchaseLinkRequest = CreateRequestParameter(_apiSettings);

        var response = await GetAsync();

        Console.WriteLine();
        ConsoleExtensions.BoxedOutput("Endpoint Bilgileri");
        ConsoleExtensions.WriteLineWithSubTitle("Endpoint Adresi : ", _apiSettings.BaseAddress + URL);
        ConsoleExtensions.WriteLineWithSubTitle("Method Tipi : ", HttpMethod.Post);
        ConsoleExtensions.BoxedOutput("Request Parametreler");
        ConsoleExtensions.WriteLineWithSubTitle("merchant_key : ", purchaseLinkRequest.merchant_key);
        ConsoleExtensions.WriteLineWithSubTitle("currency_code : ", purchaseLinkRequest.currency_code);
        ConsoleExtensions.WriteLineWithSubTitle("invoice : ", purchaseLinkRequest.invoice);
        ConsoleExtensions.WriteLineWithSubTitle("name : ", purchaseLinkRequest.name);
        ConsoleExtensions.WriteLineWithSubTitle("surname : ", purchaseLinkRequest.surname);
        ConsoleExtensions.BoxedOutput("Response Parametreler");
        if (response != null)
        {
            ConsoleExtensions.WriteLineWithSubTitle("status : ", response.status);
            ConsoleExtensions.WriteLineWithSubTitle("status_code : ", response.status_code);
            ConsoleExtensions.WriteLineWithSubTitle("success_message : ", response.success_message);
            ConsoleExtensions.WriteLineWithSubTitle("link : ", response.link);
            ConsoleExtensions.WriteLineWithSubTitle("order_id : ", response.order_id);
        }
        else
        {
            Console.WriteLine("Response alınamadı.");
        }
    }
}