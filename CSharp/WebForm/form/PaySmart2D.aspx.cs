using System;
using System.Net.Http;
using System.Text;
using System.Threading.Tasks;
using Newtonsoft.Json;
using form.Generate;
using System.Web.UI.WebControls;

namespace form
{
    public partial class PaySmart2D : System.Web.UI.Page
    {
        private const string ApiBaseUrl = "https://testapp.halkode.com.tr/ccpayment/api/";
        private const string MerchantKey = "$2y$10$XUmbnOQ0nmHsZy8WxIno4euYobTVUzxqtU1h..x32zyfG6qw7OYrq";
        private const string AppId = "f77c7d06a417638ccde51c35fd6f6c17";
        private const string AppSecret = "30296568e1d7941de4fd684dbc7203e4";

        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                installments.Items.Clear();
            }
        }

        protected async void cardNumber_TextChanged(object sender, EventArgs e)
        {
            string cardNo = cardNumber.Text.Trim();
            string totalAmountText = totalAmount.Text.Trim();

            if (string.IsNullOrEmpty(cardNo) || string.IsNullOrEmpty(totalAmountText))
            {
                return;
            }

            lblResult.Text = ""; // Önceki mesajları temizle

            // Tutar kontrolü
            if (!decimal.TryParse(totalAmountText, out decimal totalValue) || totalValue <= 0)
            {
                lblResult.Text += "<b>Hata:</b> Lütfen geçerli bir toplam tutar giriniz.<br/>";
                return;
            }

            // Token al
            string token = await GetTokenForGetPosAsync();
            if (string.IsNullOrEmpty(token))
            {
                lblResult.Text += "<b>Hata:</b> Token alınamadı.<br/>";
                return;
            }

            // GetPos API çağrısı
            var data = new
            {
                credit_card = cardNo,
                currency_code = "TRY",
                amount = totalValue,
                merchant_key = MerchantKey
            };

            using (HttpClient client = new HttpClient())
            {
                client.DefaultRequestHeaders.Authorization = new System.Net.Http.Headers.AuthenticationHeaderValue("Bearer", token);
                string json = JsonConvert.SerializeObject(data);
                StringContent content = new StringContent(json, Encoding.UTF8, "application/json");

                try
                {
                    HttpResponseMessage response = await client.PostAsync(ApiBaseUrl + "getpos", content);
                    string responseContent = await response.Content.ReadAsStringAsync();

                    if (response.IsSuccessStatusCode)
                    {
                        dynamic result = JsonConvert.DeserializeObject(responseContent);
                        if (result.status_code == 100)
                        {
                            // Taksitleri al ve dropdown'ı güncelle
                            var installmentsArray = result.data;
                            SetInstallments(installmentsArray);
                        }
                        else
                        {
                            lblResult.Text += $"<b>API Hatası:</b> {result.status_description}<br/>";
                            SetInstallments(new dynamic[] { new { installments_number = 1, title = "Tek Çekim" } }); // Hata durumunda tek çekim
                        }
                    }
                    else
                    {
                        lblResult.Text += $"<b>HTTP Hatası:</b> {response.StatusCode}<br/>";
                        SetInstallments(new dynamic[] { new { installments_number = 1, title = "Tek Çekim" } }); // Hata durumunda tek çekim
                    }
                }
                catch (Exception ex)
                {
                    lblResult.Text += $"<b>İstek Hatası:</b> {ex.Message}<br/>";
                    SetInstallments(new dynamic[] { new { installments_number = 1, title = "Tek Çekim" } }); // Hata durumunda tek çekim
                }
            }
        }

        private void SetInstallments(dynamic installmentsArray)
        {
            installments.Items.Clear();
            foreach (var installment in installmentsArray)
            {
                installments.Items.Add(new ListItem($"{installment.installments_number} Taksit - {installment.title}", installment.installments_number.ToString()));
            }

            // Eğer taksit seçeneği yoksa tek çekim ekle
            if (installments.Items.Count == 0)
            {
                installments.Items.Add(new ListItem("Tek Çekim", "1"));
            }
        }

        protected async void ProcessPayment_Click(object sender, EventArgs e)
        {
            lblResult.Text = "<b>Ödeme İşleniyor...</b><br/>";

            try
            {
                await ProcessPaymentAsync();
            }
            catch (Exception ex)
            {
                lblResult.Text += "<b>Hata:</b> " + ex.Message + "<br/>";
            }
        }

        private async Task ProcessPaymentAsync()
        {
            string token = await GetToken();
            if (string.IsNullOrEmpty(token))
            {
                lblResult.Text += "<b>Hata:</b> Token alınamadı!<br/>";
                return;
            }

            string baseUrl = "https://testapp.halkode.com.tr/ccpayment/api/paySmart2D";
            string merchantKey = "$2y$10$XUmbnOQ0nmHsZy8WxIno4euYobTVUzxqtU1h..x32zyfG6qw7OYrq";

            // Kullanıcıdan gelen verileri al
            string cardHolder = cardHolderName.Text.Trim();
            string cardNumberText = cardNumber.Text.Trim();
            string expiryMonthText = expiryMonth.Text.Trim();
            string expiryYearText = expiryYear.Text.Trim();
            string cvvText = cvv.Text.Trim();
            string invoiceNumber = GenerateRandomInvoiceNumber();
            string installmentsValue = installments.SelectedValue;
            decimal totalValue;

            if (!decimal.TryParse(totalAmount.Text, out totalValue) || totalValue <= 0)
            {
                lblResult.Text = "<b>Hata:</b> Geçersiz toplam tutar!";
                return;
            }

            string currencyCode = "TRY";

            // Hash oluştur
            HashGenerator hashGenerator = new HashGenerator();
            string hashKey = hashGenerator.GenerateHashKey(
                false,
                totalValue.ToString().Replace(",", "."),
                installmentsValue,
                currencyCode,
                merchantKey,
                invoiceNumber
            );

            var data = new
            {
                cc_holder_name = cardHolder,
                cc_no = cardNumberText,
                expiry_month = expiryMonthText,
                expiry_year = expiryYearText,
                cvv = cvvText,
                currency_code = currencyCode,
                installments_number = installmentsValue,
                invoice_id = invoiceNumber,
                invoice_description = "Ödeme Test",
                items = new[]
                {
                        new { name = "Ürün", price = totalValue, quantity = 1, description = "Satın alınan ürün" }
                    },
                total = totalValue,
                merchant_key = merchantKey,
                hash_key = hashKey
            };

            using (HttpClient client = new HttpClient())
            {
                client.DefaultRequestHeaders.Authorization = new System.Net.Http.Headers.AuthenticationHeaderValue("Bearer", token);

                string json = JsonConvert.SerializeObject(data, Formatting.Indented);
                HttpContent content = new StringContent(json, Encoding.UTF8, "application/json");

                try
                {
                    lblResult.Text += "<b>API'ye istek gönderiliyor...</b><br/>";
                    HttpResponseMessage response = await client.PostAsync(baseUrl, content);
                    string paymentResult = await response.Content.ReadAsStringAsync();

                    lblResult.Text += "<b>API Yanıtı:</b> " + paymentResult + "<br/><br/>";

                    if (!response.IsSuccessStatusCode)
                    {
                        lblResult.Text += "<b>Hata Kodu:</b> " + response.StatusCode + "<br/>";
                        lblResult.Text += "<b>Hata Mesajı:</b> " + paymentResult + "<br/>";
                    }
                }
                catch (Exception ex)
                {
                    lblResult.Text += "<b>İstek Hatası:</b> " + ex.Message + "<br/>";
                }
            }
        }

        private async Task<string> GetToken()
        {
            string baseUrl = "https://testapp.halkode.com.tr/ccpayment/api/token";
            var data = new { app_id = AppId, app_secret = AppSecret };
            string jsonData = JsonConvert.SerializeObject(data);

            using (HttpClient client = new HttpClient())
            {
                HttpContent content = new StringContent(jsonData, Encoding.UTF8, "application/json");

                try
                {
                    HttpResponseMessage response = await client.PostAsync(baseUrl, content);
                    string result = await response.Content.ReadAsStringAsync();

                    lblResult.Text += "<b>Token Yanıtı:</b> " + result + "<br/><br/>";

                    var decodedResponse = JsonConvert.DeserializeObject<dynamic>(result);
                    return decodedResponse?.status_code == 100 ? decodedResponse.data.token.ToString() : null;
                }
                catch (Exception ex)
                {
                    lblResult.Text += "<b>Token Hatası:</b> " + ex.Message + "<br/>";
                    return null;
                }
            }
        }

        private async Task<string> GetTokenForGetPosAsync()
        {
            var data = new { app_id = AppId, app_secret = AppSecret };
            string response = await SendHttpPostRequest("token", null, data);
            if (string.IsNullOrEmpty(response))
                return null;
            dynamic tokenObj = JsonConvert.DeserializeObject(response);
            return tokenObj?.status_code == 100 ? tokenObj.data.token.ToString() : null;
        }

        private static async Task<string> SendHttpPostRequest(string endpoint, string token, object data)
        {
            using (HttpClient client = new HttpClient())
            {
                if (!string.IsNullOrEmpty(token))
                    client.DefaultRequestHeaders.Authorization = new System.Net.Http.Headers.AuthenticationHeaderValue("Bearer", token);

                string jsonData = JsonConvert.SerializeObject(data, Formatting.Indented);
                HttpContent content = new StringContent(jsonData, Encoding.UTF8, "application/json");

                try
                {
                    HttpResponseMessage response = await client.PostAsync(ApiBaseUrl + endpoint, content);
                    string result = await response.Content.ReadAsStringAsync();

                    if (!response.IsSuccessStatusCode)
                    {
                        return null;
                    }

                    return result;
                }
                catch (Exception)
                {
                    return null;
                }
            }
        }


        // Rastgele Fatura Numarası Üreten Metod
        private string GenerateRandomInvoiceNumber()
        {
            Random random = new Random();
            return $"INV-{DateTime.Now:yyyyMMdd}-{random.Next(100000, 999999)}";
        }
    }
}
