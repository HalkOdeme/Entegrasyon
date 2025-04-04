using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.DependencyInjection;
using Microsoft.Extensions.Hosting;
using PlatformodePaymentIntegration.Contract.Response;
using System.Text.RegularExpressions;

namespace PlatformodePaymentIntegration.Settings;

public class ApiSettingConfiguration
{
    public ApiSettings Configuration()
    {
        using IHost host = Host.CreateDefaultBuilder(null).Build();

        IConfiguration config = host.Services.GetRequiredService<IConfiguration>();

        var app_id = config.GetValue<string?>("ApiSetting:app_id");
        var app_secret = config.GetValue<string?>("ApiSetting:app_secret");
        var base_address = config.GetValue<string?>("ApiSetting:base_address");
        var merchant_key = config.GetValue<string?>("ApiSetting:merchant_key");

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

        return new ApiSettings
        {
            AppId = app_id,
            AppSecret = app_secret,
            BaseAddress = base_address,
            MerchantKey = merchant_key
        };
    }

    bool IsValidURL(string? url)
    {
        string Pattern = @"^(?:http(s)?:\/\/)?[\w.-]+(?:\.[\w\.-]+)+[\w\-\._~:/?#[\]@!\$&'\(\)\*\+,;=.]+$";
        Regex Rgx = new Regex(Pattern, RegexOptions.Compiled | RegexOptions.IgnoreCase);
        return Rgx.IsMatch(url);
    }
}
