using HalkOdePaymentIntegration.Contract.Response;
using HalkOdePaymentIntegration.Generate;
using HalkOdePaymentIntegration.Settings;
using Microsoft.AspNetCore.Mvc;
using System.Text.Json;

namespace HalkOdePaymentIntegration.Controllers
{
    public class PurchaseLinkController : Controller
    {
        private const string URL = "purchase/link";
        private readonly HttpClient _httpClient;
        private readonly ApiSettings _apiSettings;

        public PurchaseLinkController()
        {
            _httpClient = new HttpClient();
            _apiSettings = new ApiSettingConfiguration().Configuration();
        }

        public IActionResult Index()
        {
            return View();
        }

        [HttpPost]
        public async Task<IActionResult> ProcessPayment(ApiSettings apiSettings, string name, string surname, double total)
        {
            var invoice_id = InvoiceGenerator.GenerateInvoiceId();
            var return_url = Url.Action("Index", "Succes", null, Request.Scheme);
            var requestData = new Dictionary<string, string>
            
            {
                { "merchant_key", _apiSettings.MerchantKey },
                { "currency_code", "TRY" },
                { "invoice", $"{{\"invoice_id\":\"{invoice_id}\",\"invoice_description\":\"Testdescription\",\"total\":{total},\"return_url\":\"https://www.google.com\",\"cancel_url\":\"https://github.com.tr\",\"items\":[{{\"name\":\"Item1\",\"price\":{total},\"quantity\":1,\"description\":\"Test\"}}]}}" },
                { "name", name },
                { "surname", surname }
            };

            using (var client = new HttpClient())
            {
                var content = new FormUrlEncodedContent(requestData);
                var response = await client.PostAsync($"{_apiSettings.BaseAddress}{URL}", content);
                var responseString = await response.Content.ReadAsStringAsync();

                if (!response.IsSuccessStatusCode)
                {
                    ViewBag.Error = $"Error: {response.ReasonPhrase}";
                    return View("Index");
                }

                var responseData = JsonSerializer.Deserialize<Dictionary<string, object>>(responseString);

                ViewBag.RequestData = requestData;
                ViewBag.ResponseData = responseData;
            }

            return View("Index");
        }
    }
}