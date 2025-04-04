using HalkOdePaymentIntegration.Contract.Request;
using HalkOdePaymentIntegration.Contract.Response;
using HalkOdePaymentIntegration.Generate;
using HalkOdePaymentIntegration.Settings;
using Microsoft.AspNetCore.Mvc;
using System.Text;
using System.Text.Json;

namespace HalkOdePaymentIntegration.Controllers
{
    public class CompleteController : Controller
    {
        private const string URL = "payment/complete";
        private readonly HttpClient _httpClient;
        private readonly ApiSettings _apiSettings;

        public CompleteController()
        {
            _httpClient = new HttpClient();
            _apiSettings = new ApiSettingConfiguration().Configuration();
        }

        public IActionResult Index()
        {
            return View();
        }

        [HttpPost]
        public async Task<IActionResult> ProcessPayment(string invoice_id, string order_id, string status)
        {
            var completeRequest = CreateRequestParameter(_apiSettings, invoice_id, order_id, status);
            var response = await GetAsync(completeRequest);

            ViewBag.RequestData = completeRequest;
            ViewBag.ResponseData = response;

            return View("Index");
        }

        private async Task<CompleteResponse> GetAsync(CompleteRequest completeRequest)
        {
            var tokenResponse = await new TokenController().GetAsync();
            var jsonRequest = JsonSerializer.Serialize(completeRequest);

            var httpContent = new StringContent(jsonRequest, Encoding.UTF8, "application/json");
            _httpClient.DefaultRequestHeaders.Authorization =
                new System.Net.Http.Headers.AuthenticationHeaderValue("Bearer", tokenResponse?.data?.token);

            try
            {
                var httpResponse = await _httpClient.PostAsync($"{_apiSettings.BaseAddress}{URL}", httpContent);
                var jsonResponse = await httpResponse.Content.ReadAsStringAsync();
                return JsonSerializer.Deserialize<CompleteResponse>(jsonResponse);
            }
            catch (Exception ex)
            {
                throw new Exception($"Hata: {ex.Message}");
            }
        }

        private CompleteRequest CreateRequestParameter(ApiSettings apiSettings, string invoice_id, string order_id, string status)
        {
            var completeRequest = new CompleteRequest
            {
                invoice_id = invoice_id,
                order_id = order_id,
                status = "complete",
                merchant_key = apiSettings.MerchantKey,

            };

            HashGenerator hashGenerator = new();

            completeRequest.hash_key = hashGenerator.GenerateHashKey(
                false,
                completeRequest.merchant_key,
                completeRequest.invoice_id,
                completeRequest.order_id,
                completeRequest.status);

            return completeRequest;
        }
    }
}
