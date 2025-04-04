using System;
using System.Collections.Generic;
using System.Net.Http;
using System.Threading.Tasks;
using System.Web.Mvc;
using Newtonsoft.Json;

namespace form.Controllers
{
    public class TokenController : Controller
    {
       
        private readonly HttpClient _client;
        private readonly ApiSettings _apiSettings;

        public TokenController()
        {
            _client = new HttpClient();
            _apiSettings = new ApiSettings
            {
                AppId = "f77c7d06a417638ccde51c35fd6f6c17",
                AppSecret = "30296568e1d7941de4fd684dbc7203e4",
                BaseAddress = "https://testapp.halkode.com.tr/ccpayment/",
                MerchantKey = "$2y$10$XUmbnOQ0nmHsZy8WxIno4euYobTVUzxqtU1h..x32zyfG6qw7OYrq",
                TokenUrls = "https://testapp.halkode.com.tr/ccpayment/api/token"
            };
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

                var httpRequestMessage = new HttpRequestMessage(HttpMethod.Post, $"https://testapp.halkode.com.tr/ccpayment/api/token")
                {
                    Content = new FormUrlEncodedContent(formData)
                };

                var result = await _client.SendAsync(httpRequestMessage);
                var response = await result.Content.ReadAsStringAsync();

                if (string.IsNullOrWhiteSpace(response))
                {
                    throw new Exception("Boş yanıt alındı. Lütfen API'yi kontrol ediniz.");
                }

                var tokenResponse = JsonConvert.DeserializeObject<TokenResponse>(response);

                if (tokenResponse == null || tokenResponse.data == null)
                {
                    throw new Exception("Token alınamadı. Hata: " + (tokenResponse?.status_description ?? "Bilinmeyen hata."));
                }

                return tokenResponse;
            }
            catch (Exception ex)
            {
                throw new Exception($"Token alınırken hata oluştu: {ex.Message}");
            }
        }
    }

    public class TokenResponse
    {
        public int status_code { get; set; }
        public string status_description { get; set; }
        public TokenDataResponse data { get; set; }
        public bool Success { get; internal set; }
        public string Message { get; internal set; }
    }

    public class TokenDataResponse
    {
        public string token { get; set; }
        public int is_3d { get; set; }
        public string expires_at { get; set; }
    }
}
