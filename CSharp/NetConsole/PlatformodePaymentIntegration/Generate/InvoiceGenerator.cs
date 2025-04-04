using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace PlatformodePaymentIntegration.Generate;

public static class InvoiceGenerator
{
    private static readonly Random random = new Random();
    public static string GenerateInvoiceId()
    {
        const string digits = "0123456789";
        const string letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        var stringBuilder = new StringBuilder();

        stringBuilder.Append(letters[random.Next(letters.Length)]);
        stringBuilder.Append(letters[random.Next(letters.Length)]);

        for (int i = 0; i < 5; i++)
        {
            stringBuilder.Append(digits[random.Next(digits.Length)]);
        }

        return stringBuilder.ToString();
    }
}
