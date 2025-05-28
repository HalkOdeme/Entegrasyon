using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.DependencyInjection;
using Microsoft.Extensions.Hosting;
using HalkOdePaymentIntegration.Contract.Response;
using System;
using System.Text.RegularExpressions;

namespace HalkOdePaymentIntegration.Settings
{
    public class ApiSettingConfiguration
    {
        public ApiSettings Configuration()
        {
            IHost host = Host.CreateDefaultBuilder(null).Build();
            using (host)
            {
                IConfiguration config = host.Services.GetRequiredService<IConfiguration>();

                ////test
                var base_address = "https://testapp.halkode.com.tr/ccpayment/";
                var tokenUrls = "https://testapp.halkode.com.tr/ccpayment/api/token";

                var merchant_key = "$2y$10$avMpLZvIIEY4brcULaj4u.can9eg3gAnx5s3JGz5Yxd.9zka8YfaO";//Üye İş Yeri Anahtarı --Ödeme Test Üye İşyeri
                var app_id = "de948c3eafdf5582409d0ad9a0809666"; // UYGULAMA ANAHTARI
                var app_secret = "b15fba89a18997ab32e36d0b490f9aff"; // UYGULAMA PAROLASI


                if (string.IsNullOrWhiteSpace(app_id))
                    throw new ArgumentException("app_id bilgisi bulunmadı. Lütfen appsettings.json dosyasındaki bilgileri kontrol ediniz.");

                if (string.IsNullOrWhiteSpace(app_secret))
                    throw new ArgumentException("app_secret bilgisi bulunmadı. Lütfen appsettings.json dosyasındaki bilgileri kontrol ediniz.");

                var checkBaseAddress = IsValidURL(base_address);
                if (!checkBaseAddress)
                    throw new ArgumentException("base_address bilgisi bulunmadı. Lütfen appsettings.json dosyasındaki bilgileri kontrol ediniz.");

                if (!base_address.EndsWith("/"))
                    base_address += "/";

                if (string.IsNullOrWhiteSpace(merchant_key))
                    throw new ArgumentException("merchant_key bilgisi bulunmadı. Lütfen appsettings.json dosyasındaki bilgileri kontrol ediniz.");

                if (string.IsNullOrWhiteSpace(tokenUrls))
                    throw new ArgumentException("tokenUrls bilgisi bulunmadı. Lütfen appsettings.json dosyasındaki bilgileri kontrol ediniz.");

                return new ApiSettings
                {
                    AppId = app_id,
                    AppSecret = app_secret,
                    BaseAddress = base_address,
                    MerchantKey = merchant_key,
                    TokenUrls = tokenUrls
                };
            }
        }

        bool IsValidURL(string url)
        {
            string Pattern = @"^(?:http(s)?:\/\/)?[\w.-]+(?:\.[\w\.-]+)+[\w\-\._~:/?#[\]@!\$&'\(\)\*\+,;=.]+$";
            Regex Rgx = new Regex(Pattern, RegexOptions.Compiled | RegexOptions.IgnoreCase);
            return Rgx.IsMatch(url);
        }
    }
}