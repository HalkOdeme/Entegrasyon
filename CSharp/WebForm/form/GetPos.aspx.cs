using System;
using System.Net.Http;
using System.Text;
using System.Threading.Tasks;
using Newtonsoft.Json;
using form.Generate;

namespace form
{
    public partial class GetPos : System.Web.UI.Page
    {
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

            string baseUrl = "https://testapp.halkode.com.tr/ccpayment/api/getpos";
            string merchantKey = "$2y$10$avMpLZvIIEY4brcULaj4u.can9eg3gAnx5s3JGz5Yxd.9zka8YfaO"; //Üye İş Yeri Anahtarı --Ödeme Test Üye İşyeri
   

        // Kullanıcıdan gelen verileri al

        string cardNumberText = cardNumber.Text.Trim();

            decimal totalValue;

            if (!decimal.TryParse(totalAmount.Text, out totalValue) || totalValue <= 0)
            {
                lblResult.Text = "<b>Hata:</b> Geçersiz toplam tutar!";
                return;
            }

            string currencyCode = "TRY";

   

            var data = new
            {

                credit_card = cardNumberText,

                currency_code = currencyCode,

   

                amount = totalValue,
                merchant_key = merchantKey,

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
            var data = new { app_id = "de948c3eafdf5582409d0ad9a0809666", app_secret = "b15fba89a18997ab32e36d0b490f9aff" };
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
    }
}
