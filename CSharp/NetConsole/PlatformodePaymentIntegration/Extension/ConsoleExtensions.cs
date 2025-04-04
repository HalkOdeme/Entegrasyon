namespace PlatformodePaymentIntegration.Extension
{
    public static class ConsoleExtensions
    {
        public static void Caption(string text, ConsoleColor backgroundColor = ConsoleColor.DarkRed, ConsoleColor foregroundColor = ConsoleColor.White)
        {
            Console.BackgroundColor = backgroundColor;
            Console.ForegroundColor = foregroundColor;

            Console.WriteLine(text);

            Console.ResetColor();
        }

        public static void SubTitle(string text, ConsoleColor foregroundColor = ConsoleColor.Red)
        {
            Console.ForegroundColor = foregroundColor;

            Console.Write(text);

            Console.ResetColor();
        }

        public static void WriteLineWithSubTitle(string subTitle, object? value, ConsoleColor subTitleForegroundColor = ConsoleColor.Red)
        {
            Console.ForegroundColor = subTitleForegroundColor;

            Console.Write(subTitle);
            Console.ResetColor();
            Console.WriteLine(value);
        }

        public static void BoxedOutput(string message)
        {
            int width = 100;
            string horizontalLine = new string('-', width);

            int padding = Math.Max(0, (width - message.Length - 2) / 2);
            string paddedMessage = message.PadLeft(message.Length + padding).PadRight(width - 3);

            Console.WriteLine(horizontalLine);
            Console.WriteLine($"| {paddedMessage}|");
            Console.WriteLine(horizontalLine);
        }


        public static void BoxedOutputForErrorMessage(string subTitle, string message)
        {
            int width = 100;
            string horizontalLine = new string('-', width);

            message = subTitle + message;

            int padding = Math.Max(0, (width - message.Length - 2) / 2);
            string paddedMessage = message.PadLeft(message.Length + padding).PadRight(width - 3);

            Console.WriteLine(horizontalLine);
            Console.ForegroundColor = ConsoleColor.Red;
            Console.WriteLine( $"| { paddedMessage }|");
            Console.ResetColor();
            Console.WriteLine(horizontalLine);
        }
    }
}
