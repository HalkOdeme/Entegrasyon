using Microsoft.AspNetCore.Mvc;

namespace HalkOdePaymentIntegration.Controllers
{
    public class SuccesController : Controller
    {
        public IActionResult Index(string order_no, string order_id, string invoice_id, string status_code, string transaction_type, string payment_status, string md_status)
        {
            ViewBag.OrderNo = order_no;
            ViewBag.OrderId = order_id;
            ViewBag.InvoiceId = invoice_id;
            ViewBag.StatusCode = status_code;
            ViewBag.TransactionType = transaction_type;
            ViewBag.PaymentStatus = payment_status;
            ViewBag.MdStatus = md_status;

            return View();
        }
    }
}