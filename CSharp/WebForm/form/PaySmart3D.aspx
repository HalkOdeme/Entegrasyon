<%@ Page Title="HalkOde" Language="C#" MasterPageFile="~/Site.Master" Async="true" AutoEventWireup="true" CodeBehind="PaySmart3D.aspx.cs" Inherits="form.PaySmart3D" EnableEventValidation="false" %>

<asp:Content ID="BodyContent" ContentPlaceHolderID="MainContent" runat="server">
    <style>
        .card-container {
            perspective: 1000px;
        }

        .bank-card {
            width: 360px;
            height: 220px;
            position: relative;
            transform-style: preserve-3d;
            transition: transform 0.6s;
        } 

        .bank-card .card-front,
        .bank-card .card-back {
            width: 100%;
            height: 100%;
            border-radius: 15px;
            position: absolute;
            backface-visibility: hidden;
            background: linear-gradient(to right, #3a3a3a, #000);
            color: white;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .bank-card .card-front {
            z-index: 2;
        }

        .bank-card .card-back {
            transform: rotateY(180deg);
        }

        .bank-name {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            width: 100%;
            position: absolute;
            top: 15px;
            left: 0;
        }

        .card-chip {
            width: 50px;
            height: 30px;
            background: gold;
            border-radius: 5px;
            margin: 10px 0;
        }

        .card-number {
            font-size: 22px;
            letter-spacing: 2px;
            text-align: center;
        }

        .card-holder,
        .expiry {
            font-size: 14px;
            display: flex;
            justify-content: space-between;
        }

        .black-strip {
            background: black;
            width: 100%;
            height: 40px;
            position: absolute;
            top: 20px;
        }

        .cvv-box {
            position: absolute;
            bottom: 40px;
            right: 20px;
            background: white;
            color: black;
            padding: 5px 10px;
            font-size: 18px;
            border-radius: 5px;
        }

    </style>

    <script>
        function formatCardNumber(input) {
            let value = input.replace(/\D/g, ''); // Sadece rakamları al
            return value.replace(/(\d{4})/g, '$1 ').trim(); // 4 haneli gruplara ayır
        }

        function updateCard() {
            document.getElementById("displayCardHolder").innerText = document.getElementById("<%= cardHolderName.ClientID %>").value || "AD SOYAD";
            document.getElementById("displayCardNumber").innerText = formatCardNumber(document.getElementById("<%= cardNumber.ClientID %>").value) || "**** **** **** ****";
            let month = document.getElementById("<%= expiryMonth.ClientID %>").value;
            let year = document.getElementById("<%= expiryYear.ClientID %>").value;
            document.getElementById("displayExpiry").innerText = (month && year) ? `${month}/${year}` : "MM/YY";
            document.getElementById("displayCVV").innerText = document.getElementById("<%= cvv.ClientID %>").value || "***";
        }

        function flipCard(showBack) {
            document.getElementById("bankCard").style.transform = showBack ? "rotateY(180deg)" : "rotateY(0deg)";
        }
    </script>

    <div class="container">
        <h2 class="text-center mt-4">3D Ödeme Formu</h2>
         
        <div class="row">
            <!-- Sol Taraf: Ödeme Formu -->
            <div class="col-md-6">
                <div class="card p-4 mt-3 shadow">
                    <label>Kart Sahibi Adı:</label>
                    <asp:TextBox ID="cardHolderName" CssClass="form-control" runat="server" onkeyup="updateCard()"></asp:TextBox>

                    <label>Toplam Tutar (TRY):</label>
                    <asp:TextBox ID="totalAmount" CssClass="form-control" runat="server"></asp:TextBox>

                    <label>Kart Numarası:</label>
                    <asp:TextBox ID="cardNumber" CssClass="form-control" runat="server" AutoPostBack="true" OnTextChanged="cardNumber_TextChanged" onkeyup="updateCard()"></asp:TextBox>

                    <label>Son Kullanma Ayı:</label>
                    <asp:TextBox ID="expiryMonth" CssClass="form-control" runat="server" onkeyup="updateCard()"></asp:TextBox>

                    <label>Son Kullanma Yılı:</label>
                    <asp:TextBox ID="expiryYear" CssClass="form-control" runat="server" onkeyup="updateCard()"></asp:TextBox>

                    <label>CVV:</label>
                    <asp:TextBox ID="cvv" CssClass="form-control" runat="server" onkeyup="updateCard()" onfocus="flipCard(true)" onblur="flipCard(false)"></asp:TextBox>


                    <label>Taksit Sayısı:</label>
                    <asp:DropDownList ID="installments" CssClass="form-control" runat="server">
                        <asp:ListItem Value="1" Selected="True">Tek Çekim</asp:ListItem>
                    </asp:DropDownList>

                    <asp:Button ID="btnPay" CssClass="btn btn-primary mt-3 w-100" runat="server" Text="Ödeme Yap" OnClick="ProcessPayment_Click" />

                    <div class="result mt-3">
                        <asp:Label ID="lblResult" CssClass="alert alert-info d-block text-center" runat="server" Text=""></asp:Label>
                    </div>
                </div>
            </div>

            <!-- Sağ Taraf: Kart Silüeti -->
            <div class="col-md-6 d-flex align-items-center justify-content-center">
                <div class="card-container">
                    <div class="bank-card" id="bankCard">
                        <div class="card-front">
                            <div class="bank-name">BANKA KARTI</div>
                            <div class="card-chip"></div>
                            <div class="card-number" id="displayCardNumber">**** **** **** ****</div>
                            <div class="card-holder">
                                <span>Kart Sahibi</span>
                                <div id="displayCardHolder">AD SOYAD</div>
                            </div>
                            <div class="expiry">
                                <span>Son Kullanma</span>
                                <div id="displayExpiry">MM/YY</div>
                            </div>
                        </div>
                        <div class="card-back">
                            <div class="black-strip"></div>
                            <div class="cvv-box">
                                <span>CVV</span>
                                <div id="displayCVV">***</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="apiResponseContainer" runat="server"></div>
    </div>
</asp:Content>
