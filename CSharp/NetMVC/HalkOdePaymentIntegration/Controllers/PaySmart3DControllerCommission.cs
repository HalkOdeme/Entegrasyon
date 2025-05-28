
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
    public class PaySmart3DControllerCommission : Controller
    {
        private const string URL = "api/paySmart3D";
        private readonly HttpClient _httpClient;
        private readonly ApiSettings _apiSettings;

        public PaySmart3DControllerCommission()
        {
            _httpClient = new HttpClient();
            _apiSettings = new ApiSettingConfiguration().Configuration();
        }

        public IActionResult Index()
        {
            return View();
        }

        [HttpPost("GetInstallmentsss")]
        public async Task<JsonResult> GetInstallmentsss([FromBody] InstallmentRequestsss request)
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

                return ParseInstallmentsResponse(responseContent);
            }
            catch (Exception ex)
            {
                // Hata loglama
                Console.WriteLine($"GetInstallmentsss metodu sırasında hata oluştu: {ex.Message}");
                return Json(new { error = $"Sunucu hatası: {ex.Message}" });
            }
        }


        [HttpPost("GetComission")]
        public async Task<JsonResult> GetComission([FromBody] InstallmentRequestsss request)
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
                    currency_code = "TRY",
                    
                };

                var responseContent = await PostAsync($"{_apiSettings.BaseAddress}api/commissions", requestData, token);
                if (responseContent == null)
                {
                    return Json(new { error = "API'den yanıt alınamadı." });
                }

                return ParseInstallmentsResponse(responseContent);
            }
            catch (Exception ex)
            {
                // Hata loglama
                Console.WriteLine($"GetComission metodu sırasında hata oluştu: {ex.Message}");
                return Json(new { error = $"Sunucu hatası: {ex.Message}" });
            }
        }


        [HttpPost]
        public async Task<IActionResult> ProcessPayment(
            string cc_holder_name,
            string cc_no,
            string expiry_month,
            string expiry_year,
            string cvv,
            int installments_number,
            string invoice_description,
            string commission_by,
            string is_commission_from_user,
            string payment_completed_by,
            double total,
            string transaction_type)
        {
            var return_url = Url.Action("Index", "Succes", null, Request.Scheme);
            var cancel_url = Url.Action("Index", "Fail", null, Request.Scheme);

            var paySmart3DRequest = CreateRequestParameter(
                _apiSettings,
                cc_holder_name,
                cc_no,
                expiry_month,
                expiry_year,
                cvv,
                installments_number,
                invoice_description,
                total,
                transaction_type,
                payment_completed_by,
                commission_by,
                is_commission_from_user,
                return_url,
                cancel_url);

            var response = await Send3DPaymentRequestAsync(paySmart3DRequest);

            ViewBag.RequestData = paySmart3DRequest;
            ViewBag.ResponseData = response;

            return View("Index");
        }

        private async Task<string> GetTokenAsync()
        {
            var tokenResponse = await new TokenController().GetAsync();
            return tokenResponse?.data?.token;
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

        private JsonResult ParseInstallmentsResponse(string responseContent)
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

        private async Task<string> Send3DPaymentRequestAsync(PaySmart3DRequest paySmart3DRequest)
        {
            var token = await GetTokenAsync();
            if (string.IsNullOrEmpty(token))
            {
                return "Token alınamadı.";
            }

            var responseContent = await PostAsync($"{_apiSettings.BaseAddress}{URL}", paySmart3DRequest, token);
            if (responseContent == null)
            {
                return "API'den yanıt alınamadı.";
            }

            if (responseContent.StartsWith("<"))
            {
                return responseContent;
            }

            var jsonResponse = JsonSerializer.Deserialize<Dictionary<string, string>>(responseContent);
            return JsonSerializer.Serialize(jsonResponse);
        }

        private PaySmart3DRequest CreateRequestParameter(
            ApiSettings apiSettings,
            string cc_holder_name,
            string cc_no,
            string expiry_month,
            string expiry_year,
            string cvv,
            int installments_number,
            string invoice_description,
            double total,
            string transaction_type,
            string payment_completed_by,
            string is_commission_from_user,
            string commission_by,
            string return_url,
            string cancel_url)
        {
            var paySmart3DRequest = new PaySmart3DRequest
            {
                cc_holder_name = cc_holder_name,
                cc_no = cc_no,
                expiry_month = expiry_month,
                expiry_year = expiry_year,
                currency_code = "TRY",
                installments_number = installments_number,
                invoice_id = InvoiceGenerator.GenerateInvoiceId(),
                invoice_description = invoice_description,
                total = total,
                items = new List<Item3D>
                {
                    new Item3D
                    {
                        name = "Item3",
                        price = total,
                        quantity = 1,
                        description = invoice_description
                    }
                },
                name = "John",
                surname = "Dao",
                return_url = return_url,
                cancel_url = cancel_url,
                payment_completed_by = "app",
                cvv = cvv,
                merchant_key = apiSettings.MerchantKey,
                transaction_type = transaction_type,
                commission_by = "user",  //Komisyon'u Üye işyeri karşılayacaksa "merchant", son kullanıcı karşılayacaksa "user" gönderilmelidir. 
                is_commission_from_user ="1",
            };

            var hashGenerator = new HashGenerator();
            paySmart3DRequest.hash_key = hashGenerator.GenerateHashKey(
                false,
                paySmart3DRequest.total.ToString().Replace(",", "."),
                paySmart3DRequest.installments_number.ToString(),
                paySmart3DRequest.currency_code,
                paySmart3DRequest.merchant_key,
                paySmart3DRequest.invoice_id);

            return paySmart3DRequest;
        }
    }
    
    public class InstallmentRequestsss
    {
        public string CardNumber { get; set; }
        public string TotalAmount { get; set; }
    }
}

