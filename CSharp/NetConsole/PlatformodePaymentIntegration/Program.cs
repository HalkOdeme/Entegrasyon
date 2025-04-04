namespace PlatformodePaymentIntegration
{
    internal class Program
    {
        static async Task Main(string[] args)
        {
            await ShowMenu();

            return;
        }

        async static Task ShowMenu()
        {
            while (true)
            {
                Console.Clear();
                Console.WriteLine("Lütfen aşağıdaki menüden bir numara seçiniz:");
                Console.WriteLine("1) Token");
                Console.WriteLine("2) Pay Smart 2D");
                Console.WriteLine("3) Pay Smart 3D");
                Console.WriteLine("4) Purchase Link");
                Console.WriteLine("5) Refund Api");
                Console.WriteLine("6) Check Status");
                Console.WriteLine("7) Confirm Payment");
                Console.WriteLine("8) Installment");
                Console.WriteLine("9) Commissions");
                Console.WriteLine("10) Getpos");
                Console.WriteLine("11) GetTransactions");
                Console.WriteLine("12) Alltransaction");
                Console.WriteLine("13) Payment Complete");
                Console.WriteLine("14) Çıkış");

                Console.Write("Lütfen seçiminizi yapınız [1-14]: ");

                int choice;
                while (!int.TryParse(Console.ReadLine(), out choice) || choice < 1 || choice > 14)
                {
                    Console.WriteLine("Geçersiz bir seçim yaptınız. Lütfen tekrar deneyiniz.");
                    Console.Write("Lütfen seçiminizi yapınız [1-14]: ");
                }

                if (choice == 14)
                {
                    Console.WriteLine("Programdan çıkılıyor...");
                    break;
                }

                await ProcessChoice(choice);


                Console.WriteLine();
                Console.WriteLine();
                Console.WriteLine("Menüye yeniden dönmek için herhangi bir tuşa basınız...");
                Console.ReadKey();

            }
        }

        static async Task ProcessChoice(int choice)
        {
            if (choice == 1)
            {
                await new TokenApi().PrintAsync();
            }
            else if (choice == 2)
            {
                await new PaySmart2D().PrintAsync();
            }
            else if (choice == 3)
            {
                PaySmart3D paySmart3D = new();
                await paySmart3D.PrintAsync();
                paySmart3D.PrintHtml();
            }
            else if (choice == 4)
            {
                await new PurchaseLink().PrintAsync();
            }
            else if (choice == 5)
            {
                Console.Write("Lütfen iade ediecek invoice_id bilgisini giriniz: ");
                var invoice_id = Console.ReadLine();

                await new RefundApi().PrintAsync(invoice_id);
            }
            else if (choice == 6)
            {
                Console.Write("Lütfen durumunu inceleyeceğiniz invoice_id bilgisini giriniz: ");
                var invoice_id = Console.ReadLine();

                await new CheckStatusApi().PrintAsync(invoice_id);
            }
            else if (choice == 7)
            {
                Console.Write("Onaylayacağınız ödemenin invoice_id bilgisini giriniz: ");
                var invoice_id = Console.ReadLine();

                await new ConfirmPaymentApi().PrintAsync(invoice_id);
            }
            else if (choice == 8)
            {
                await new InstallmentApi().PrintAsync();
            }
            else if (choice == 9)
            {
                await new CommissionApi().PrintAsync();
            }
            else if (choice == 10)
            {
                await new GetPosApi().PrintAsync();
            }
            else if (choice == 11)
            {
                Console.Write("Tarih bilgisini Y-m-d formatına göre giriniz (Örn; \"2024-04-05\"): ");
                var date = Console.ReadLine();

                await new GetTransactionApi().PrintAsync(date);
            }
            else if (choice == 12)
            {
                await new AllTransactionApi().PrintAsync();
            }
            else if (choice == 13)
            {
                Console.Write("Tamamlamak istediğiniz ödemenin invoice_id bilgisini giriniz (Örn; \"Abc1234\"): ");
                var invoice_id = Console.ReadLine();
                Console.Write("Tamamlamak istediğiniz ödemenin order_id bilgisini giriniz (Örn; \"VP17123239825285705\"): ");
                var order_id = Console.ReadLine();
                Console.Write("Tamamlamak istediğiniz ödemenin statu bilgisini giriniz (Örn; \"complete\"): ");
                var status = Console.ReadLine();

                await new CompleteApi().PrintAsync(invoice_id, order_id, status);
            }
        }
    }
}