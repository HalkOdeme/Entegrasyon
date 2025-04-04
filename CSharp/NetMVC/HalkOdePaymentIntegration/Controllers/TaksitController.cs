using HalkOdePaymentIntegration.Contract.Request;
using HalkOdePaymentIntegration.Contract.Response;
using HalkOdePaymentIntegration.Generate;
using HalkOdePaymentIntegration.Settings;
using Microsoft.AspNetCore.Mvc;
using Newtonsoft.Json;
using System.Text;

namespace HalkOdePaymentIntegration.Controllers
{
    public class TaksitController : Controller
    {
        private const string URL = "api/installments";
        private readonly HttpClient _httpClient;
        private readonly ApiSettings _apiSettings;

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
        public async Task<IActionResult> ProcessPayment(string merchant_key)
        {
            var installmentRequest = CreateRequestParameter(_apiSettings, merchant_key);
            var installmentResponse = await GetAsync(installmentRequest);

            ViewBag.RequestData = installmentRequest;
            ViewBag.ResponseData = installmentResponse;
             
            return View("Index");
        }

        private async Task<InstallmentResponse> GetAsync(InstallmentRequest installmentRequest)
        {
            var tokenResponse = await new TokenController().GetAsync();
            var jsonRequest = JsonConvert.SerializeObject(installmentRequest);

            var httpContent = new StringContent(jsonRequest, Encoding.UTF8, "application/json");
            _httpClient.DefaultRequestHeaders.Authorization =
                new System.Net.Http.Headers.AuthenticationHeaderValue("Bearer", tokenResponse?.data?.token);

            try
            {
                var httpResponse = await _httpClient.PostAsync($"{_apiSettings.BaseAddress}{URL}", httpContent);
                var jsonResponse = await httpResponse.Content.ReadAsStringAsync();
                return JsonConvert.DeserializeObject<InstallmentResponse>(jsonResponse);
            }
            catch (Exception ex)
            {
                throw new Exception($"Hata: {ex.Message}");
            }
        }

        private InstallmentRequest CreateRequestParameter(ApiSettings apiSettings, string merchant_key)
        {
            return new InstallmentRequest
            {
                merchant_key = "$2y$10$12Cg9.DfqlXZQpRbUbE.zuORaObIk4KV7HKs4PcOPTIh0WrEa47l.",
            };
        }
    }
}