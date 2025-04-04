using PlatformodePaymentIntegration.Settings;
using System.Security.Cryptography;
using System.Text;

namespace PlatformodePaymentIntegration.Generate;

public class HashGenerator
{
    public string GenerateHashKey(bool replaceAppSecretWithMmerchantKey, params string[] subjects)
    {
        StringBuilder builder = new StringBuilder();
        for (int i = 0; i < subjects.Length; i++)
        {
            //if (string.IsNullOrEmpty(subjects[i]))
            //    continue;

            builder.Append(subjects[i]);

            if (i < subjects.Length - 1)
                builder.Append('|');
        }

        string data = builder.ToString();

        Random mt_rand = new Random();

        string iv = Sha1Hash(mt_rand.Next().ToString()).Substring(0, 16);

        var apiSettings = new ApiSettingConfiguration().Configuration();
        string passwordKey = replaceAppSecretWithMmerchantKey ? apiSettings.MerchantKey : apiSettings.AppSecret;

        string password = Sha1Hash(passwordKey);
        string salt = Sha1Hash(mt_rand.Next().ToString()).Substring(0, 4);
        string saltWithPassword = "";

        using (SHA256 sha256Hash = SHA256.Create())
        {
            saltWithPassword = GetHash(sha256Hash, password + salt);
        }

        string encrypted = Encryptor(data, saltWithPassword.Substring(0, 32), iv);

        string msg_encrypted_bundle = iv + ":" + salt + ":" + encrypted;
        msg_encrypted_bundle = msg_encrypted_bundle.Replace("/", "__");

        return msg_encrypted_bundle;
    }

    public string GenerateRefundHashKey(string amount, string invoice_id, string merchant_key, string app_secret)
    {
        string data = amount + "|" + invoice_id + "|" + merchant_key;

        Random mt_rand = new Random();

        string iv = Sha1Hash(mt_rand.Next().ToString()).Substring(0, 16);

        string password = Sha1Hash(app_secret);

        string salt = Sha1Hash(mt_rand.Next().ToString()).Substring(0, 4);

        string saltWithPassword = "";

        using (SHA256 sha256Hash = SHA256.Create())
        {
            saltWithPassword = GetHash(sha256Hash, password + salt);
        }

        string encrypted = Encryptor(data, saltWithPassword.Substring(0, 32), iv);

        string msg_encrypted_bundle = iv + ":" + salt + ":" + encrypted;
        msg_encrypted_bundle = msg_encrypted_bundle.Replace("/", "__");

        return msg_encrypted_bundle;
    }


    private string GetHash(HashAlgorithm hashAlgorithm, string input)
    {
        byte[] data = hashAlgorithm.ComputeHash(Encoding.UTF8.GetBytes(input));
        var sBuilder = new StringBuilder();

        for (int i = 0; i < data.Length; i++)
        {
            sBuilder.Append(data[i].ToString("x2"));
        }

        return sBuilder.ToString();
    }

    private string Sha1Hash(string password)
    {
        return string.Join("", SHA1.Create().ComputeHash(Encoding.UTF8.GetBytes(password)).Select(x => x.ToString("x2")));
    }

    private string Encryptor(string TextToEncrypt, string strKey, string strIV)
    {
        byte[] PlainTextBytes = Encoding.UTF8.GetBytes(TextToEncrypt);

        AesCryptoServiceProvider aesProvider = new AesCryptoServiceProvider();
        aesProvider.BlockSize = 128;
        aesProvider.KeySize = 256;
        aesProvider.Key = Encoding.UTF8.GetBytes(strKey);
        aesProvider.IV = Encoding.UTF8.GetBytes(strIV);
        aesProvider.Padding = PaddingMode.PKCS7;
        aesProvider.Mode = CipherMode.CBC;

        ICryptoTransform cryptoTransform = aesProvider.CreateEncryptor(aesProvider.Key, aesProvider.IV);
        byte[] EncryptedBytes = cryptoTransform.TransformFinalBlock(PlainTextBytes, 0, PlainTextBytes.Length);

        return Convert.ToBase64String(EncryptedBytes);
    }
}
