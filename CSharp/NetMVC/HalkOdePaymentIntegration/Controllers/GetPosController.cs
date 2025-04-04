using HalkOdePaymentIntegration.Contract.Request;
using HalkOdePaymentIntegration.Contract.Response;
using HalkOdePaymentIntegration.Generate;
using HalkOdePaymentIntegration.Settings;
using Microsoft.AspNetCore.Mvc;
using Newtonsoft.Json;
using System.Text;
using System.Dynamic;
using DocumentFormat.OpenXml.InkML;

namespace HalkOdePaymentIntegration.Controllers
{
    public class GetPosController : Controller
    {
        private const string URL = "api/getpos";
        private readonly HttpClient _httpClient;
        private readonly ApiSettings _apiSettings;

        public GetPosController()
        {
            _httpClient = new HttpClient();
            _apiSettings = new ApiSettingConfiguration().Configuration();
        }

        public IActionResult Index()
        {
            return View();
        }

        

          
        [HttpPost]
        public async Task<IActionResult> ProcessPayment(string credit_card, double amount, string currency_code, string logo)
        {
            if (amount <= 0)
            {
                ModelState.AddModelError("amount", "The amount must be greater than 0.");
                return View("Index");
            }

            var getPosRequest = CreateRequestParameter(_apiSettings, credit_card, currency_code, amount, logo);
            var response = await GetAsync(getPosRequest);

            ViewBag.RequestData = getPosRequest;
            ViewBag.ResponseData = response;

            return View("Index");
        }

        private async Task<GetPosResponse> GetAsync(GetPosRequest getPosRequest)
        {
            var tokenResponse = await new TokenController().GetAsync();
            var jsonRequest = JsonConvert.SerializeObject(getPosRequest);

            var httpContent = new StringContent(jsonRequest, Encoding.UTF8, "application/json");
            _httpClient.DefaultRequestHeaders.Authorization =
                new System.Net.Http.Headers.AuthenticationHeaderValue("Bearer", tokenResponse?.data?.token);

            try
            {
                var httpResponse = await _httpClient.PostAsync($"{_apiSettings.BaseAddress}{URL}", httpContent);
                var jsonResponse = await httpResponse.Content.ReadAsStringAsync();
                return JsonConvert.DeserializeObject<GetPosResponse>(jsonResponse);
            }
            catch (Exception ex)
            {
                throw new Exception($"Hata: {ex.Message}");
            }
        }


        private GetPosRequest CreateRequestParameter(ApiSettings apiSettings, string credit_card, string currency_code, double amount, string logo)
        {
            return new GetPosRequest
            {
                credit_card = credit_card,
                currency_code = "TRY",
                amount = amount,
                merchant_key = apiSettings.MerchantKey,
                
            };
        }
    }
}