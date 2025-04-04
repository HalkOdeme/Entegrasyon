using HalkOdePaymentIntegration.Contract.Request;
using HalkOdePaymentIntegration.Contract.Response;
using HalkOdePaymentIntegration.Generate;
using HalkOdePaymentIntegration.Settings;
using Microsoft.AspNetCore.Mvc;
using System.Net.Http.Headers;
using System.Text;
using System.Text.Json;

namespace HalkOdePaymentIntegration.Controllers
{
    public class PaySmart2DController : Controller
    {
        private const string URL = "api/paySmart2D";  // 2D Endpoint
        private readonly HttpClient _httpClient;
        private readonly ApiSettings _apiSettings;

        public PaySmart2DController()
        {
            _httpClient = new HttpClient();
            _apiSettings = new ApiSettingConfiguration().Configuration();
        }

        public IActionResult Index()
        {
            return View();
        }

        [HttpPost]
        public async Task<IActionResult> ProcessPayment(
            string cc_holder_name,
            string cc_no,
            string expiry_month,
            string expiry_year,
            string cvv,
            string currency_code,
            int installments_number,
            string invoice_description,
            double total,
            string item_name,
            double item_price,
            int item_quantity,
            string item_description,
            string name,
            string surname,
            string transaction_type
        )
        {
       
            if (total <= 0)
            {
                ModelState.AddModelError("total", "Tutar 0'dan büyük olmalıdır.");
                return View("Index");
            }

            var paySmart2DRequest = CreateRequestParameter(
                _apiSettings,
                cc_holder_name,
                cc_no,
                expiry_month,
                expiry_year,
                cvv,
                currency_code,
                installments_number,
                invoice_description,
                total,
                item_name,
                item_price,
                item_quantity,
                item_description,
                name,
                surname,
                transaction_type
              
            );

            var response = await GetAsync(paySmart2DRequest);

            ViewBag.RequestData = paySmart2DRequest;
            ViewBag.ResponseData = response;

            return View("Index");
        }

        private async Task<string> PostAsync(string url, object data, string token)
        {
            var jsonRequest = JsonSerializer.Serialize(data);
            var httpContent = new StringContent(jsonRequest, Encoding.UTF8, "application/json");
            _httpClient.DefaultRequestHeaders.Authorization = new AuthenticationHeaderValue("Bearer", token);

            try
            {
                var httpResponse = await _httpClient.PostAsync(url, httpContent);
                if (!httpResponse.IsSuccessStatusCode)
                {
                    return null;
                }

                return await httpResponse.Content.ReadAsStringAsync();
            }
            catch (Exception ex)
            {
                Console.WriteLine($"İstek sırasında hata oluştu: {ex.Message}");
                return null;
            }
        }

        private JsonResult ParseInstallmentsResponses(string responseContent)
        {
            try
            {
                using (var document = JsonDocument.Parse(responseContent))
                {
                    if (document.RootElement.TryGetProperty("status_code", out JsonElement statusCodeEl)
                        && statusCodeEl.GetInt32() == 100)
                    {
                        if (document.RootElement.TryGetProperty("data", out JsonElement dataEl)
                            && dataEl.ValueKind == JsonValueKind.Array)
                        {
                            var installmentsList = new List<object>();

                            foreach (var item in dataEl.EnumerateArray())
                            {
                                int installmentsNumber = item.GetProperty("installments_number").GetInt32();
                                string title = item.GetProperty("title").ToString();
                                decimal payableAmount = item.GetProperty("payable_amount").GetDecimal();
                                string currencyCode = item.GetProperty("currency_code").GetString();

                                installmentsList.Add(new
                                {
                                    installments_number = installmentsNumber,
                                    title = title,
                                    payable_amount = payableAmount,
                                    currency_code = currencyCode
                                });
                            }

                            return Json(new { status = "success", data = installmentsList });
                        }
                        else
                        {
                            return Json(new { error = "Beklenen taksit verisi bulunamadı." });
                        }
                    }
                    else
                    {
                        return Json(new { error = "API'den başarısız yanıt alındı (status_code != 100)." });
                    }
                }
            }
            catch (Exception ex)
            {
                return Json(new { error = $"Yanıt işlenirken hata oluştu: {ex.Message}" });
            }
        }

        [HttpPost("GetInstallmentss")]
        public async Task<JsonResult> GetInstallmentss([FromBody] InstallmentRequestss request)
        {
            try
            {
                if (string.IsNullOrWhiteSpace(request.CardNumber) || string.IsNullOrWhiteSpace(request.TotalAmount))
                {
                    return Json(new { error = "Kart numarası veya tutar bilgisi boş olamaz." });
                }

                if (!decimal.TryParse(request.TotalAmount, out decimal totalValue) || totalValue <= 0)
                {
                    return Json(new { error = "Geçerli bir tutar giriniz." });
                }

                var token = await GetTokenAsync();
                if (string.IsNullOrEmpty(token))
                {
                    return Json(new { error = "Token alınamadı." });
                }

                var requestData = new
                {
                    credit_card = request.CardNumber,
                    currency_code = "TRY",
                    amount = totalValue,
                    merchant_key = _apiSettings.MerchantKey
                };

                var responseContent = await PostAsync($"{_apiSettings.BaseAddress}api/getpos", requestData, token);
                if (responseContent == null)
                {
                    return Json(new { error = "API'den yanıt alınamadı." });
                }

                return ParseInstallmentsResponses(responseContent);
            }
            catch (Exception ex)
            {
                // Hata loglama
                Console.WriteLine($"GetInstallments metodu sırasında hata oluştu: {ex.Message}");
                return Json(new { error = $"Sunucu hatası: {ex.Message}" });
            }
        }

        private async Task<string> GetTokenAsync()
        {
            var tokenResponse = await new TokenController().GetAsync();
            return tokenResponse?.data?.token;
        }

        private async Task<PaySmart2DResponse> GetAsync(PaySmart2DRequest paySmart2DRequest)
        {
         
            var tokenResponse = await new TokenController().GetAsync();
            var token = tokenResponse?.data?.token;
            if (string.IsNullOrEmpty(token))
            {
                throw new Exception("Token alınamadı.");
            }

       
            var jsonRequest = JsonSerializer.Serialize(paySmart2DRequest);
            var httpContent = new StringContent(jsonRequest, Encoding.UTF8, "application/json");

         
            _httpClient.DefaultRequestHeaders.Authorization =
                new System.Net.Http.Headers.AuthenticationHeaderValue("Bearer", token);

            try
            {
               
                var endpointUrl = $"{_apiSettings.BaseAddress}{URL}";
                Console.WriteLine($"PaySmart2D Endpoint: {endpointUrl}");

                var httpResponse = await _httpClient.PostAsync(endpointUrl, httpContent);
                var jsonResponse = await httpResponse.Content.ReadAsStringAsync();

            
                Console.WriteLine("PaySmart2D Response: " + jsonResponse);

                
                return JsonSerializer.Deserialize<PaySmart2DResponse>(jsonResponse);
            }
            catch (Exception ex)
            {
                throw new Exception($"Hata: {ex.Message}");
            }
        }

        private PaySmart2DRequest CreateRequestParameter(
            ApiSettings apiSettings,
            string cc_holder_name,
            string cc_no,
            string expiry_month,
            string expiry_year,
            string cvv,
            string currency_code,
            int installments_number,
            string invoice_description,
            double total,
            string item_name,
            double item_price,
            int item_quantity,
            string item_description,
            string name,
            string surname,
            string transaction_type

        )
        {
            // Request oluştur
            var paySmart2DRequest = new PaySmart2DRequest
            {
                cc_holder_name = cc_holder_name,
                cc_no = cc_no,
                expiry_month = expiry_month,
                expiry_year = expiry_year,
                cvv = cvv,
                currency_code = "TRY",
                installments_number = installments_number,
                invoice_id = InvoiceGenerator.GenerateInvoiceId(),
                invoice_description = invoice_description,
                total = total,
                items = new List<Item2D>
                 {
                  new Item2D { name = "item", price = total, quantity = 1, description = invoice_description }
                 },
                name = "John",
                surname = "Dao",
                merchant_key = apiSettings.MerchantKey,
                transaction_type= transaction_type

            };

            // Hash key oluştur
            var hashGenerator = new HashGenerator();
            paySmart2DRequest.hash_key = hashGenerator.GenerateHashKey(
                false,
                paySmart2DRequest.total.ToString().Replace(",", "."),
                paySmart2DRequest.installments_number.ToString(),
                paySmart2DRequest.currency_code,
                paySmart2DRequest.merchant_key,
                paySmart2DRequest.invoice_id
            );

            return paySmart2DRequest;
        }
    }
    public class InstallmentRequestss
    {
        public string CardNumber { get; set; }
        public string TotalAmount { get; set; }
    }
}
