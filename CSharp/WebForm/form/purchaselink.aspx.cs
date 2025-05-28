using System;
using System.Net.Http;
using System.Text;
using System.Threading.Tasks;
using Newtonsoft.Json;

namespace form
{
    public partial class Purchaselink : System.Web.UI.Page
    {
        private const string BaseUrl = "https://testapp.halkode.com.tr/ccpayment";
        private const string MerchantKey = "$2y$10$avMpLZvIIEY4brcULaj4u.can9eg3gAnx5s3JGz5Yxd.9zka8YfaO"; //Üye İş Yeri Anahtarı --Ödeme Test Üye İşyeri
        private const string AppId = "de948c3eafdf5582409d0ad9a0809666"; // UYGULAMA ANAHTARI
        private const string AppSecret = "b15fba89a18997ab32e36d0b490f9aff"; // UYGULAMA PAROLAS

        protected async void ProcessPayment_Click(object sender, EventArgs e)
        {
            lblResult.Text = "<b>Ödeme İşleniyor...</b><br/>";

            try
            {
                await ProcessPaymentAsync();
            }
            catch (Exception ex)
            {
                lblResult.Text += $"<b>Hata:</b> {ex.Message}<br/>";
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

            // Kullanıcıdan gelen verileri al
            string cardHolder = cardHolderName.Text?.Trim();
            string invoiceNumber = GenerateRandomInvoiceNumber();
            if (!decimal.TryParse(totalAmount.Text, out decimal totalValue) || totalValue <= 0)
            {
                lblResult.Text = "<b>Hata:</b> Geçersiz toplam tutar!";
                return;
            }

            // ✅ Invoice JSON'u düz bir string olarak gönderme
            var invoiceData = new
            {
                invoice_id = invoiceNumber,
                invoice_description = "Testdescription",
                total = totalValue,
                return_url = "https://www.google.com",
                cancel_url = "https://github.com.tr",
                items = new[]
                {
                        new { name = "Item1", price = totalValue, quantity = 1, description = "Test" }
                    }
            };

            string invoiceJson = JsonConvert.SerializeObject(invoiceData); // ✅ DÜZ STRING FORMATINDA JSON OLUŞTURMA

            var requestData = new
            {
                cc_holder_name = cardHolder,
                invoice_id = invoiceNumber,
                invoice_description = "Ödeme Test",
                total = totalValue,
                merchant_key = MerchantKey,
                currency_code = "TRY",
                invoice = invoiceJson // ✅ Düz string JSON olarak ekleniyor
            };

            using (HttpClient client = new HttpClient())
            {
                client.DefaultRequestHeaders.Authorization = new System.Net.Http.Headers.AuthenticationHeaderValue("Bearer", token);
                string json = JsonConvert.SerializeObject(requestData, Formatting.Indented);
                HttpContent content = new StringContent(json, Encoding.UTF8, "application/json");

                try
                {
                    lblResult.Text += "<b>API'ye istek gönderiliyor...</b><br/>";
                    HttpResponseMessage response = await client.PostAsync($"{BaseUrl}/purchase/link", content);
                    string paymentResult = await response.Content.ReadAsStringAsync();

                    lblResult.Text += $"<b>API Yanıtı:</b> {paymentResult}<br/><br/>";

                    var responseData = JsonConvert.DeserializeObject<dynamic>(paymentResult);
                    string paymentUrl = responseData?.link; // API'den gelen link

                    if (string.IsNullOrEmpty(paymentUrl))
                    {
                        lblResult.Text += "<b>Hata:</b> Ödeme bağlantısı oluşturulamadı! (payment_url boş)<br/>";
                    }
                    else
                    {
                        // 🔥 TIKLANABİLİR LİNK OLUŞTURULDU
                        lblResult.Text += $"<b>Ödeme Bağlantısı:</b> <a href='{paymentUrl}' target='_blank'>{paymentUrl}</a><br/>";
                    }

                    if (!response.IsSuccessStatusCode)
                    {
                        lblResult.Text += $"<b>Hata Kodu:</b> {response.StatusCode}<br/>";
                        lblResult.Text += $"<b>Hata Mesajı:</b> {paymentResult}<br/>";
                    }
                }
                catch (Exception ex)
                {
                    lblResult.Text += $"<b>İstek Hatası:</b> {ex.Message}<br/>";
                }
            }
        }

        private async Task<string> GetToken()
        {
            var requestData = new { app_id = AppId, app_secret = AppSecret };
            string jsonData = JsonConvert.SerializeObject(requestData);

            using (HttpClient client = new HttpClient())
            {
                HttpContent content = new StringContent(jsonData, Encoding.UTF8, "application/json");

                try
                {
                    HttpResponseMessage response = await client.PostAsync($"{BaseUrl}/api/token", content);
                    string result = await response.Content.ReadAsStringAsync();

                    lblResult.Text += $"<b>Token Yanıtı:</b> {result}<br/><br/>";

                    var decodedResponse = JsonConvert.DeserializeObject<dynamic>(result);
                    return decodedResponse?.status_code == 100 ? decodedResponse.data.token.ToString() : null;
                }
                catch (Exception ex)
                {
                    lblResult.Text += $"<b>Token Hatası:</b> {ex.Message}<br/>";
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
