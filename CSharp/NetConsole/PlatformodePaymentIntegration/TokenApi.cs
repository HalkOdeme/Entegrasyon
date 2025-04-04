using PlatformodePaymentIntegration.Contract.Response;
using PlatformodePaymentIntegration.Extension;
using PlatformodePaymentIntegration.Settings;
using System.Text.Json;

namespace PlatformodePaymentIntegration;

public class TokenApi
{
    private const string URL = "api/token";

    private readonly HttpClient _client;
    private readonly ApiSettings _apiSettings;

    public TokenApi()
    {
        _client = new HttpClient();
        _apiSettings = new ApiSettingConfiguration().Configuration();
    }

    public async Task<TokenResponse?> GetAsync()
    {
        try
        {
            var formData = new Dictionary<string, string>
            {
                { "app_id", _apiSettings.AppId },
                { "app_secret", _apiSettings.AppSecret }
            };

            var httpRequestMessage = new HttpRequestMessage(HttpMethod.Post, $"{_apiSettings.BaseAddress}{URL}")
            {
                Content = new FormUrlEncodedContent(formData)
            };

            var result = await _client.SendAsync(httpRequestMessage);
            var response = await result.Content.ReadAsStringAsync();
            var tokenResponse = JsonSerializer.Deserialize<TokenResponse>(response);

            if (tokenResponse is null)
            {
                var message = "Token bilgisi alınamadı. Lütfen appsettings.json dosyasındaki bilgileri kontrol ediniz.";
                ConsoleExtensions.BoxedOutputForErrorMessage("HATA : ", message);
                throw new ArgumentNullException(message);
            }

            if (tokenResponse.data is null)
            {
                ConsoleExtensions.BoxedOutputForErrorMessage("HATA : " , tokenResponse.status_description);
                throw new ArgumentNullException(tokenResponse.status_description);
            }

            return tokenResponse;
        }
        catch (Exception ex) when (!(ex is ArgumentNullException))
        {
            var message = $"Token alınırken beklenmedik bir hata oluştu. Lütfen sistem yöneticisine başvurunuz.({ex.Message})";
            ConsoleExtensions.BoxedOutputForErrorMessage("", message);
            throw;
        }
    }

    public async Task PrintAsync()
    {
        var response = await GetAsync();

        Console.WriteLine();
        ConsoleExtensions.BoxedOutput("Endpoint Bilgileri");
        ConsoleExtensions.WriteLineWithSubTitle("Endpoint Adresi : ", _apiSettings.BaseAddress + URL);
        ConsoleExtensions.WriteLineWithSubTitle("Method Tipi : ", HttpMethod.Post);
        ConsoleExtensions.BoxedOutput("Request Parametreler");
        ConsoleExtensions.WriteLineWithSubTitle("app_id : ", _apiSettings.AppId);
        ConsoleExtensions.WriteLineWithSubTitle("app_secret : ", _apiSettings.AppSecret);
        ConsoleExtensions.BoxedOutput("Response Parametreler");
        if (response != null)
        {
            ConsoleExtensions.WriteLineWithSubTitle("status_code : ", response.status_code);
            ConsoleExtensions.WriteLineWithSubTitle("status_description : ", response.status_description);
            ConsoleExtensions.WriteLineWithSubTitle("token : ", response.data?.token);
            ConsoleExtensions.WriteLineWithSubTitle("is_3d : ", response.data?.is_3d);
            ConsoleExtensions.WriteLineWithSubTitle("expires_at : ", response.data?.expires_at);
        }
        else
        {
            Console.WriteLine("Response alınamadı.");
        }
    }
}
