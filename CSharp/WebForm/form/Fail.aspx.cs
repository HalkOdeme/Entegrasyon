using System;

namespace form
{
    public partial class Fail : System.Web.UI.Page
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                // QueryString'den verileri alırken null kontrolü yapalım
                string errorCode = Request.QueryString["error_code"] ?? "Bilinmiyor";
                string errorMessage = Request.QueryString["error_message"] ?? "Hata açıklaması bulunamadı";
                string statusDescription = Request.QueryString["status_description"] ?? "Durum açıklaması bulunamadı";

                lblErrorCode.Text = $"Hata Kodu: {errorCode}";
                lblErrorMessage.Text = $"Hata Açıklaması: {errorMessage}";
                lblStatusDescription.Text = $"Durum Açıklaması: {statusDescription}";
            }
        }
    }
}
