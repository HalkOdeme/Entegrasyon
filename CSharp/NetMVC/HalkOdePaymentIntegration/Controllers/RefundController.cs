using HalkOdePaymentIntegration.Contract.Request;
using HalkOdePaymentIntegration.Contract.Response;
using HalkOdePaymentIntegration.Generate;
using HalkOdePaymentIntegration.Settings;
using Microsoft.AspNetCore.Mvc;
using System.Text;
using System.Text.Json;

namespace HalkOdePaymentIntegration.Controllers
{
    public class RefundController : Controller
    {
        private const string URL = "api/refund";
        private readonly HttpClient _httpClient;
        private readonly ApiSettings _apiSettings;

        public RefundController()
        {
            _httpClient = new HttpClient();
            _apiSettings = new ApiSettingConfiguration().Configuration();
        }

        public IActionResult Index()
        {
            return View();
        }

        [HttpPost]
        public async Task<IActionResult> ProcessPayment(double amount,string invoice_id)
        {
            if (amount <= 0)
            {
                ModelState.AddModelError("total", "The total must be greater than 0.");
                return View("Index");
            }
 
            var refundRequest = CreateRequestParameter(_apiSettings, amount, invoice_id);
            var response = await GetAsync(refundRequest);

            ViewBag.RequestData = refundRequest;
            ViewBag.ResponseData = response;

            return View("Index");
        }

        private async Task<RefundResponse> GetAsync(RefundRequest refundRequest)
        {
            var tokenResponse = await new TokenController().GetAsync();
            var jsonRequest = JsonSerializer.Serialize(refundRequest);

            var httpContent = new StringContent(jsonRequest, Encoding.UTF8, "application/json");
            _httpClient.DefaultRequestHeaders.Authorization =
                new System.Net.Http.Headers.AuthenticationHeaderValue("Bearer", tokenResponse?.data?.token);

            try
            {
                var httpResponse = await _httpClient.PostAsync($"{_apiSettings.BaseAddress}{URL}", httpContent);
                var jsonResponse = await httpResponse.Content.ReadAsStringAsync();
                return JsonSerializer.Deserialize<RefundResponse>(jsonResponse);
            }
            catch (Exception ex)
            {
                throw new Exception($"Hata: {ex.Message}");
            }
        }

        private RefundRequest CreateRequestParameter(ApiSettings apiSettings, double amount ,string invoice_id)
        {
            var refundRequest = new RefundRequest
            {
                amount= amount,
                invoice_id = invoice_id,
                merchant_key = apiSettings.MerchantKey,
          
            };
            return refundRequest;
        }
    }
}
