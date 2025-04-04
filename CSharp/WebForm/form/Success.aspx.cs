using System;
using System.Web;

namespace form
{
    public partial class Success : System.Web.UI.Page
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            if (!IsPostBack)
            {
                // Query string'den değerleri al
                string cardNumber = Request.QueryString["credit_card_no"];
                string invoiceId = Request.QueryString["invoice_id"];
                string statusCode = Request.QueryString["status_code"];
                string statusDescription = Request.QueryString["status_description"];

                // Değerleri ilgili alanlara ata
                lblCardNumber.Text = cardNumber;
                lblInvoiceId.Text = invoiceId;
                lblStatusCode.Text = statusCode;
                lblStatusDescription.Text = statusDescription;
            }
        }
    }
}

