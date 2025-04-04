namespace form
{
    public class TokenResponse
    {
        public int status_code { get; set; }
        public string status_description { get; set; }
        public TokenDataResponse data { get; set; }
        public bool Success { get; internal set; }
        public string Message { get; internal set; }
    }

    public class TokenDataResponse
    {
        public string token { get; set; }
        public int is_3d { get; set; }
        public string expires_at { get; set; }
    }
}
