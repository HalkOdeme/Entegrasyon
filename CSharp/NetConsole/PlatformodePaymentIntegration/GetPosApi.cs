using PlatformodePaymentIntegration.Contract.Request;
using PlatformodePaymentIntegration.Contract.Response;
using PlatformodePaymentIntegration.Extension;
using PlatformodePaymentIntegration.Settings;
using System.Text.Json;

namespace PlatformodePaymentIntegration;

public class GetPosApi
{
    private const string URL = "api/getpos";

    private readonly HttpClient _httpClient;
    private readonly ApiSettings _apiSettings;

    public GetPosApi()
    {
        _httpClient = new HttpClient();
        _apiSettings = new ApiSettingConfiguration().Configuration();
    }

    private async Task<GetPosResponse?> GetAsync()
    {
        var tokenResponse = await new TokenApi().GetAsync();

        GetPosRequest getPosRequest = CreateRequestParameter(_apiSettings);

        var jsonRequest = JsonSerializer.Serialize(getPosRequest);

        var httpContent = new StringContent(jsonRequest, System.Text.Encoding.UTF8, "application/json");

        _httpClient.DefaultRequestHeaders.Authorization = new System.Net.Http.Headers.AuthenticationHeaderValue("Bearer", tokenResponse?.data?.token);

        try
        {
            var httpResponse = await _httpClient.PostAsync($"{_apiSettings.BaseAddress}{URL}", httpContent);

            var jsonResponse = await httpResponse.Content.ReadAsStringAsync();

            return JsonSerializer.Deserialize<GetPosResponse>(jsonResponse);
        }
        catch (Exception ex)
        {
            var message = $"Beklenmedik bir hata oluştu. Lütfen gönderdiğiniz parametreleri kontrol ediniz ya da sistem yöneticisine başvurunuz.({ex.Message})";
            ConsoleExtensions.BoxedOutputForErrorMessage("", message);
            throw;
        }
    }

    private GetPosRequest CreateRequestParameter(ApiSettings apiSettings)
    {
        GetPosRequest getPosRequest = new()
        {
            credit_card = "540061",
            amount = 100.00,
            currency_code = "TRY",
            merchant_key = apiSettings.MerchantKey
        };

        return getPosRequest;
    }

    public async Task PrintAsync()
    {
        GetPosRequest getPosRequest = CreateRequestParameter(_apiSettings);

        var response = await GetAsync();

        Console.WriteLine();
        ConsoleExtensions.BoxedOutput("Endpoint Bilgileri");
        ConsoleExtensions.WriteLineWithSubTitle("Endpoint Adresi : ", _apiSettings.BaseAddress + URL);
        ConsoleExtensions.WriteLineWithSubTitle("Method Tipi : ", HttpMethod.Post);
        ConsoleExtensions.BoxedOutput("Request Parametreler");
        ConsoleExtensions.WriteLineWithSubTitle("credit_card : ", getPosRequest.credit_card);
        ConsoleExtensions.WriteLineWithSubTitle("amount : ", getPosRequest.amount);
        ConsoleExtensions.WriteLineWithSubTitle("currency_code : ", getPosRequest.currency_code);
        ConsoleExtensions.WriteLineWithSubTitle("merchant_key : ", getPosRequest.merchant_key);
        ConsoleExtensions.BoxedOutput("Response Parametreler");

        if (response != null)
        {
            ConsoleExtensions.WriteLineWithSubTitle("status_code : ", response.status_code);
            ConsoleExtensions.WriteLineWithSubTitle("status_description : ", response.status_description);
            ConsoleExtensions.WriteLineWithSubTitle("data : ", $"{response.data.Count()} kayıt bulundu");

            int i = 1;
            foreach (var item in response.data)
            {
                ConsoleExtensions.WriteLineWithSubTitle($"--------------- {i++}. Kayıt ---------------", null);
                ConsoleExtensions.WriteLineWithSubTitle($"pos_id : ", item.pos_id);
                ConsoleExtensions.WriteLineWithSubTitle($"campaign_id : ", item.campaign_id);
                ConsoleExtensions.WriteLineWithSubTitle($"allocation_id : ", item.allocation_id);
                ConsoleExtensions.WriteLineWithSubTitle($"installments_number : ", item.installments_number);
                ConsoleExtensions.WriteLineWithSubTitle($"card_type : ", item.card_type);
                ConsoleExtensions.WriteLineWithSubTitle($"card_program : ", item.card_program);
                ConsoleExtensions.WriteLineWithSubTitle($"card_scheme : ", item.card_scheme);
                ConsoleExtensions.WriteLineWithSubTitle($"payable_amount : ", item.payable_amount);
                ConsoleExtensions.WriteLineWithSubTitle($"hash_key : ", item.hash_key);
                ConsoleExtensions.WriteLineWithSubTitle($"amount_to_be_paid : ", item.amount_to_be_paid);
                ConsoleExtensions.WriteLineWithSubTitle($"currency_code : ", item.currency_code);
                ConsoleExtensions.WriteLineWithSubTitle($"currency_id : ", item.currency_id);
                ConsoleExtensions.WriteLineWithSubTitle($"title : ", item.title);
            }
        }
        else
        {
            Console.WriteLine("Response alınamadı.");
        }
    }
}