using HalkOdePaymentIntegration.Contract.Request;
using HalkOdePaymentIntegration.Contract.Response;
using HalkOdePaymentIntegration.Settings;
using Microsoft.AspNetCore.Mvc;
using System.Text;
using System.Text.Json;

namespace HalkOdePaymentIntegration.Controllers
{
    public class CheckStatusController : Controller
    {
        private const string URL = "api/checkstatus";
        private readonly HttpClient _httpClient;
        private readonly ApiSettings _apiSettings;

        public CheckStatusController()
        {
            _httpClient = new HttpClient();
            _apiSettings = new ApiSettingConfiguration().Configuration();
        }

        public IActionResult Index()
        {
            return View();
        }

        [HttpPost]
        public async Task<IActionResult> ProcessPayment(string invoice_id)
        {
            
            var checkStatusRequest = CreateRequestParameter(_apiSettings, invoice_id);
            var response = await GetAsync(checkStatusRequest);

            ViewBag.RequestData = checkStatusRequest;
            ViewBag.ResponseData = response;

            return View("Index");
        }

        private async Task<CheckStatusResponse> GetAsync(CheckStatusRequest checkStatusRequest)
        {
            var tokenResponse = await new TokenController().GetAsync();
            var jsonRequest = JsonSerializer.Serialize(checkStatusRequest);

            var httpContent = new StringContent(jsonRequest, Encoding.UTF8, "application/json");
            _httpClient.DefaultRequestHeaders.Authorization =
                new System.Net.Http.Headers.AuthenticationHeaderValue("Bearer", tokenResponse?.data?.token);

            try
            {
                var httpResponse = await _httpClient.PostAsync($"{_apiSettings.BaseAddress}{URL}", httpContent);
                var jsonResponse = await httpResponse.Content.ReadAsStringAsync();
                return JsonSerializer.Deserialize<CheckStatusResponse>(jsonResponse);
            }
            catch (Exception ex)
            {
                throw new Exception($"Hata: {ex.Message}");
            }
        }

        private CheckStatusRequest CreateRequestParameter(ApiSettings apiSettings,string invoice_id)
        {
            var checkStatusRequest = new CheckStatusRequest
            {
                invoice_id = invoice_id,
                merchant_key = apiSettings.MerchantKey,
            };
            return checkStatusRequest;
        }
    }
}
