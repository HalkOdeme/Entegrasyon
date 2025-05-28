using HalkOdePaymentIntegration.Contract.Response;
using HalkOdePaymentIntegration.Settings;
using Microsoft.AspNetCore.Mvc;
using Newtonsoft.Json;
using System.Text;

namespace HalkOdePaymentIntegration.Controllers
{
    public class TaksitController : Controller
    {
        private const string URL = "api/installments";
        public readonly HttpClient _httpClient;
        public readonly ApiSettings _apiSettings;

        public TaksitController()
        {
            _httpClient = new HttpClient();
            _apiSettings = new ApiSettingConfiguration().Configuration();
        }

        public IActionResult Index()
        {
            return View();
        }

        [HttpPost]
        public async Task<IActionResult> Index(string merchant_key)
        {
            if (string.IsNullOrEmpty(merchant_key))
            {
                ModelState.AddModelError("merchant_key", "merchantkey gereklidir.");
                return View();
            }

            var token = await GetTokenAsync();
            if (string.IsNullOrEmpty(token))
            {
                ViewBag.Error = "Token alınamadı. Lütfen bilgilerinizi kontrol ediniz.";
                return View();
            }

            var data = new
            {
                merchant_key = merchant_key
            };

            ViewBag.RequestData = data;

            var jsonResponse = await PostDataAsync($"{_apiSettings.BaseAddress}{URL}", data, token);
            ViewBag.ResponseData = JsonConvert.DeserializeObject(jsonResponse);

            return View();
        }

        private async Task<string> GetTokenAsync()
        {
            var tokenUrl = _apiSettings.TokenUrls;
            var data = new
            {
                app_id = _apiSettings.AppId,
                app_secret = _apiSettings.AppSecret
            };

            using (var client = new HttpClient())
            {
                var jsonRequest = JsonConvert.SerializeObject(data);
                var httpContent = new StringContent(jsonRequest, Encoding.UTF8, "application/json");

                var response = await client.PostAsync(tokenUrl, httpContent);
                var jsonResponse = await response.Content.ReadAsStringAsync();
                var tokenResponse = JsonConvert.DeserializeObject<dynamic>(jsonResponse);

                if (tokenResponse.status_code == 100)
                {
                    return tokenResponse.data.token;
                }
                return null;
            }
        }

        private async Task<string> PostDataAsync(string url, object data, string token)
        {
            using (var client = new HttpClient())
            {
                var jsonRequest = JsonConvert.SerializeObject(data);
                var httpContent = new StringContent(jsonRequest, Encoding.UTF8, "application/json");
                client.DefaultRequestHeaders.Authorization = new System.Net.Http.Headers.AuthenticationHeaderValue("Bearer", token);

                var response = await client.PostAsync(url, httpContent);
                return await response.Content.ReadAsStringAsync();
            }
        }
    }
}