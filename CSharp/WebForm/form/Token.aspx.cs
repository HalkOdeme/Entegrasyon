using System;
using System.Web.Services;
using form.Controllers; // TokenController ve TokenResponse buradaysa bunu kullan

namespace form
{
    public partial class Token : System.Web.UI.Page
    {
        [WebMethod]
        public static form.Controllers.TokenResponse GetToken()
        {
            try
            {
                var tokenController = new TokenController();
                return tokenController.GetAsync().GetAwaiter().GetResult(); // Senkron çağrı
            }
            catch (Exception ex)
            {
                return new form.Controllers.TokenResponse
                {
                    Success = false,
                    Message = $"Token alınırken hata oluştu: {ex.Message}"
                };
            }
        }
    }
}
