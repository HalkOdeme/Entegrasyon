<%@ Page Title="Home Page" Language="C#" MasterPageFile="~/Site.Master" AutoEventWireup="true" CodeBehind="Default.aspx.cs" Inherits="form._Default" %>

<asp:Content ID="BodyContent" ContentPlaceHolderID="MainContent" runat="server">
    <style>
        /* Sayfanın tümünü kaplayan arkaplan */
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
        }

        /* İçerik alanı */
        .content {
            position: relative;
            width: 100%;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: flex-start; /* Üst kısma hizalar */
            align-items: center; /* Yatayda ortalar */
            text-align: center;
            padding-top: 40px; /* Header'dan boşluk bırakır */
            z-index: 1;
        }

        /* Arkaplan Resmi (Tüm sayfayı kaplayacak) */
        .background-image {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('mainslider.jpg') no-repeat center center;
            background-size: cover;
            z-index: -1;
        }

        /* Hoşgeldiniz Yazısı (Üste hizalanmış) */
        .welcome-text {
            font-size: 3rem;
            font-weight: bold;
            color: white;
            text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.7); /* Yazıyı belirginleştir */
        }

        /* Footer (Sabitlenmiş, mavi renkli) */
        footer {
            background-color: #180554; /* Mavi */
            color: white;
            text-align: center;
            padding: 10px 0;
            position: fixed;
            width: 100%;
            bottom: 0;
            z-index: 10;
        }
    </style>

    <div class="background-image"></div> <!-- Arka plan resmi tüm sayfayı kaplar -->

    <div class="content">
         <span style="color:white"><h2>Halk Elektronik Para ve Ödeme Hizmetleri</h2></span>
         <span style="color:white"><h2>.Net Entegrasyonuna Hoş Geldiniz</h2></span>
      
    </div>

</asp:Content>
