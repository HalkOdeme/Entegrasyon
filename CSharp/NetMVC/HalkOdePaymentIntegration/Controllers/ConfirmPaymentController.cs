using HalkOdePaymentIntegration.Contract.Request;
using HalkOdePaymentIntegration.Contract.Response;
using HalkOdePaymentIntegration.Generate;
using HalkOdePaymentIntegration.Settings;
using Microsoft.AspNetCore.Mvc;
using System.Text;
using System.Text.Json;

namespace HalkOdePaymentIntegration.Controllers
{
    public class ConfirmPaymentController : Controller
    {
        private const string URL = "api/confirmPayment";
        private readonly HttpClient _httpClient;
        private readonly ApiSettings _apiSettings;

        public ConfirmPaymentController()
        {
            _httpClient = new HttpClient();
            _apiSettings = new ApiSettingConfiguration().Configuration();
        }

        public IActionResult Index()
        {
            return View();
        }

        [HttpPost]
        public async Task<IActionResult> ProcessPayment(string invoice_id,  double total, string status)
        {
            if (total <= 0)
            {
                ModelState.AddModelError("total", "The total must be greater than 0.");
                return View("Index");
            }

            var confirmPaymentRequest = CreateRequestParameter(_apiSettings, invoice_id, total, status);
            var response = await GetAsync(confirmPaymentRequest);

            ViewBag.RequestData = confirmPaymentRequest;
            ViewBag.ResponseData = response;

            return View("Index");
        }

        private async Task<ConfirmPaymentResponse> GetAsync(ConfirmPaymentRequest confirmPaymentRequest)
        {
            var tokenResponse = await new TokenController().GetAsync();
            var jsonRequest = JsonSerializer.Serialize(confirmPaymentRequest);

            var httpContent = new StringContent(jsonRequest, Encoding.UTF8, "application/json");
            _httpClient.DefaultRequestHeaders.Authorization =
                new System.Net.Http.Headers.AuthenticationHeaderValue("Bearer", tokenResponse?.data?.token);

            try
            {
                var httpResponse = await _httpClient.PostAsync($"{_apiSettings.BaseAddress}{URL}", httpContent);
                var jsonResponse = await httpResponse.Content.ReadAsStringAsync();
                return JsonSerializer.Deserialize<ConfirmPaymentResponse>(jsonResponse);
            }
            catch (Exception ex)
            {
                throw new Exception($"Hata: {ex.Message}");
            }
        }

        private ConfirmPaymentRequest CreateRequestParameter(ApiSettings apiSettings, string invoice_id, double total, string status)
        {
            var confirmPaymentRequest = new ConfirmPaymentRequest
            {
                total = total,
                invoice_id = invoice_id,
                status="1",
                merchant_key = apiSettings.MerchantKey,
           
            };

            HashGenerator hashGenerator = new();

            confirmPaymentRequest.hash_key = hashGenerator.GenerateHashKey(
                false,
               confirmPaymentRequest.merchant_key,
                confirmPaymentRequest.invoice_id,
                confirmPaymentRequest.status);

            return confirmPaymentRequest;
        }
    }
}
