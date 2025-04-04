using PlatformodePaymentIntegration.Contract.Request;
using PlatformodePaymentIntegration.Contract.Response;
using PlatformodePaymentIntegration.Extension;
using PlatformodePaymentIntegration.Generate;
using PlatformodePaymentIntegration.Settings;
using System.Text.Json;

namespace PlatformodePaymentIntegration;

public class PaySmart2D
{
    private const string URL = "api/paySmart2D";

    private readonly HttpClient _httpClient;
    private readonly ApiSettings _apiSettings;

    public PaySmart2D()
    {
        _httpClient = new HttpClient();
        _apiSettings = new ApiSettingConfiguration().Configuration();
    }

    private async Task<PaySmart2DResponse?> GetAsync()
    {
        var tokenResponse = await new TokenApi().GetAsync();

        PaySmart2DRequest paySmart2DRequest = CreateRequestParameter(_apiSettings);

        var jsonRequest = JsonSerializer.Serialize(paySmart2DRequest);

        var httpContent = new StringContent(jsonRequest, System.Text.Encoding.UTF8, "application/json");

        _httpClient.DefaultRequestHeaders.Authorization = new System.Net.Http.Headers.AuthenticationHeaderValue("Bearer", tokenResponse?.data?.token);

        try
        {
            var httpResponse = await _httpClient.PostAsync($"{_apiSettings.BaseAddress}{URL}", httpContent);

            var jsonResponse = await httpResponse.Content.ReadAsStringAsync();

            return JsonSerializer.Deserialize<PaySmart2DResponse>(jsonResponse);
        }
        catch (Exception ex)
        {
            var message = $"Beklenmedik bir hata oluştu. Lütfen gönderdiğiniz parametreleri kontrol ediniz ya da sistem yöneticisine başvurunuz.({ex.Message})";
            ConsoleExtensions.BoxedOutputForErrorMessage("", message);
            throw;
        }
    }

    private PaySmart2DRequest CreateRequestParameter(ApiSettings apiSettings)
    {
        PaySmart2DRequest paySmart2DRequest = new()
        {
            cc_holder_name = "Test kart",
            cc_no = "4132260000000003",
            expiry_month = "12",
            expiry_year = "2024",
            cvv = "555",
            currency_code = "TRY",
            installments_number = 1,
            invoice_id = InvoiceGenerator.GenerateInvoiceId(),
            invoice_description = "INVOICE TEST DESCRIPTION",
            total = 16.43,
            items = new List<Item2D>
            {
                new Item2D()
                {
                    name = "item",
                    price = 16.43,
                    quantity = 1,
                    description = "item description"
                }
            },
            name = "John",
            surname = "Dao",
            merchant_key = apiSettings.MerchantKey
        };

        HashGenerator hashGenerator = new();

        paySmart2DRequest.hash_key = hashGenerator.GenerateHashKey(
            false,
            paySmart2DRequest.total.ToString().Replace(",","."),
            paySmart2DRequest.installments_number.ToString(),
            paySmart2DRequest.currency_code,
            paySmart2DRequest.merchant_key,
            paySmart2DRequest.invoice_id);

        return paySmart2DRequest;
    }

    public async Task PrintAsync()
    {
        PaySmart2DRequest paySmart2DRequest = CreateRequestParameter(_apiSettings);

        var response = await GetAsync();

        Console.WriteLine();
        ConsoleExtensions.BoxedOutput("Endpoint Bilgileri");
        ConsoleExtensions.WriteLineWithSubTitle("Endpoint Adresi : ", _apiSettings.BaseAddress + URL);
        ConsoleExtensions.WriteLineWithSubTitle("Method Tipi : ", HttpMethod.Post);
        ConsoleExtensions.BoxedOutput("Request Parametreler");
        ConsoleExtensions.WriteLineWithSubTitle($"cc_holder_name : ", paySmart2DRequest.cc_holder_name);
        ConsoleExtensions.WriteLineWithSubTitle($"cc_no : ", paySmart2DRequest.cc_no);
        ConsoleExtensions.WriteLineWithSubTitle($"expiry_month : ", paySmart2DRequest.expiry_month);
        ConsoleExtensions.WriteLineWithSubTitle($"expiry_year : ", paySmart2DRequest.expiry_year);
        ConsoleExtensions.WriteLineWithSubTitle($"cvv : ", paySmart2DRequest.cvv);
        ConsoleExtensions.WriteLineWithSubTitle($"currency_code : ", paySmart2DRequest.currency_code);
        ConsoleExtensions.WriteLineWithSubTitle($"installments_number : ", paySmart2DRequest.installments_number);
        ConsoleExtensions.WriteLineWithSubTitle($"invoice_id : ", paySmart2DRequest.invoice_id);
        ConsoleExtensions.WriteLineWithSubTitle($"invoice_description : ", paySmart2DRequest.invoice_description);
        ConsoleExtensions.WriteLineWithSubTitle($"total : ", paySmart2DRequest.total);
        ConsoleExtensions.WriteLineWithSubTitle($"items : ", JsonSerializer.Serialize(paySmart2DRequest.items));
        ConsoleExtensions.WriteLineWithSubTitle($"name : ", paySmart2DRequest.name);
        ConsoleExtensions.WriteLineWithSubTitle($"surname : ", paySmart2DRequest.surname);
        ConsoleExtensions.WriteLineWithSubTitle($"merchant_key : ", paySmart2DRequest.merchant_key);
        ConsoleExtensions.WriteLineWithSubTitle($"hash_key : ", paySmart2DRequest.hash_key);
        ConsoleExtensions.BoxedOutput("Response Parametreler");

        if (response != null)
        {
            ConsoleExtensions.WriteLineWithSubTitle($"status_code : ", response.status_code);
            ConsoleExtensions.WriteLineWithSubTitle($"status_description : ", response.status_description);
            ConsoleExtensions.WriteLineWithSubTitle($"order_no : ", response.data?.order_no);
            ConsoleExtensions.WriteLineWithSubTitle($"order_id : ", response.data?.order_id);
            ConsoleExtensions.WriteLineWithSubTitle($"invoice_id : ", response.data?.invoice_id);
            ConsoleExtensions.WriteLineWithSubTitle($"credit_card_no : ", response.data?.credit_card_no);
            ConsoleExtensions.WriteLineWithSubTitle($"transaction_type : ", response.data?.transaction_type);
            ConsoleExtensions.WriteLineWithSubTitle($"payment_status : ", response.data?.payment_status);
            ConsoleExtensions.WriteLineWithSubTitle($"payment_method : ", response.data?.payment_method);
            ConsoleExtensions.WriteLineWithSubTitle($"error_code : ", response.data?.error_code);
            ConsoleExtensions.WriteLineWithSubTitle($"error : ", response.data?.error);
            ConsoleExtensions.WriteLineWithSubTitle($"auth_code : ", response.data?.auth_code);
            ConsoleExtensions.WriteLineWithSubTitle($"merchant_commission : ", response.data?.merchant_commission);
            ConsoleExtensions.WriteLineWithSubTitle($"user_commission : ", response.data?.user_commission);
            ConsoleExtensions.WriteLineWithSubTitle($"merchant_commission_percentage : ", response.data?.merchant_commission_percentage);
            ConsoleExtensions.WriteLineWithSubTitle($"merchant_commission_fixed : ", response.data?.merchant_commission_fixed);
            ConsoleExtensions.WriteLineWithSubTitle($"hash_key : ", response.data?.hash_key);
            ConsoleExtensions.WriteLineWithSubTitle($"original_bank_error_code : ", response.data?.original_bank_error_code);
            ConsoleExtensions.WriteLineWithSubTitle($"original_bank_error_description : ", response.data?.original_bank_error_description);
        }
        else
        {
            Console.WriteLine("Response alınamadı.");
        }
    }
}