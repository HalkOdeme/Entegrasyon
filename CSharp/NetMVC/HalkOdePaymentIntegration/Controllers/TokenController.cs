using HalkOdePaymentIntegration.Contract.Response;
using HalkOdePaymentIntegration.Settings;
using System.Text.Json;
using Microsoft.AspNetCore.Mvc;

namespace HalkOdePaymentIntegration
{
    public class TokenController : Controller
    {
        private const string URL = "api/token";
        private readonly HttpClient _client;
        private readonly ApiSettings _apiSettings;

        public TokenController()
        {
            _client = new HttpClient();
            _apiSettings = new ApiSettingConfiguration().Configuration();
        }

        public IActionResult Index()
        {
            return View();
        }

        [HttpPost]

        public async Task<TokenResponse> GetAsync()
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

                if (tokenResponse == null)
                {
                    throw new Exception("Token bilgisi alınamadı. Lütfen appsettings.json dosyasındaki bilgileri kontrol ediniz.");
                }

                if (tokenResponse.data == null)
                {
                    throw new Exception(tokenResponse.status_description);
                }

                return tokenResponse;
            }
            catch (Exception ex)
            {
                throw new Exception($"Token alınırken beklenmedik bir hata oluştu. Lütfen sistem yöneticisine başvurunuz.({ex.Message})");
            }

        }
    }
}