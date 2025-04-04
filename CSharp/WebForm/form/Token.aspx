<%@ Page Language="C#" AutoEventWireup="true" CodeBehind="Token.aspx.cs" Inherits="form.Token" %>

<!DOCTYPE html>
<html lang="tr">
<head runat="server">
    <meta charset="utf-8" />
    <title>Token Al</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h2>API Token Al</h2>
    <button id="btnGetToken">Token Al</button>
    <p id="tokenResult"></p>

    <script>
        $(document).ready(function () {
            $("#btnGetToken").click(function () {
                $.ajax({
                    type: "POST",
                    url: "Token.aspx/GetToken",
                    contentType: "application/json; charset=utf-8",
                    dataType: "json",
                    success: function (response) {
                        if (response.d && response.d.data && response.d.data.token) {
                            localStorage.setItem("authToken", response.d.data.token);
                            $("#tokenResult").text("✅ Token alındı: " + response.d.data.token);
                        } else {
                            $("#tokenResult").text("⚠️ Token alınamadı.");
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error("❌ Token alma hatası:", xhr.responseText);
                        $("#tokenResult").text("Hata: " + xhr.responseText);
                    }
                });
            });
        });
    </script>
</body>
</html>
