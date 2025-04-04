using System;
using System.Configuration;
using System.Text.RegularExpressions;

namespace form.Settings
{
    public class ApiSettingConfiguration
    {
        public ApiSettings Configuration()
        {
            var app_id = "f77c7d06a417638ccde51c35fd6f6c17";
            var app_secret = "30296568e1d7941de4fd684dbc7203e4";
            var base_address = "https://testapp.halkode.com.tr/ccpayment";
            var merchant_key = "$2y$10$XUmbnOQ0nmHsZy8WxIno4euYobTVUzxqtU1h..x32zyfG6qw7OYrq";
       

            if (string.IsNullOrWhiteSpace(app_id))
                throw new ArgumentException("AppId bilgisi eksik. Lütfen Web.config dosyanızı kontrol ediniz.");

            if (string.IsNullOrWhiteSpace(app_secret))
                throw new ArgumentException("AppSecret bilgisi eksik. Lütfen Web.config dosyanızı kontrol ediniz.");

            if (!IsValidURL(base_address))
                throw new ArgumentException("BaseAddress bilgisi hatalı veya eksik. Lütfen Web.config dosyanızı kontrol ediniz.");

            if (!base_address.EndsWith("/"))
                base_address += "/";

            if (string.IsNullOrWhiteSpace(merchant_key))
                throw new ArgumentException("MerchantKey bilgisi eksik. Lütfen Web.config dosyanızı kontrol ediniz.");

  
            return new ApiSettings
            {
                AppId = app_id,
                AppSecret = app_secret,
                BaseAddress = base_address,
                MerchantKey = merchant_key,
               
            };
        }

        private bool IsValidURL(string url)
        {
            string pattern = @"^(https?:\/\/)?[\w.-]+(?:\.[\w\.-]+)+[\w\-\._~:/?#[\]@!\$&'\(\)\*\+,;=.]+$";
            return Regex.IsMatch(url, pattern, RegexOptions.IgnoreCase);
        }
    }

    public class ApiSettings
    {
        public string AppId { get; set; }
        public string AppSecret { get; set; }
        public string BaseAddress { get; set; }
        public string MerchantKey { get; set; }
        public string TokenUrls { get; set; }
    }
}
