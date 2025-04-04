

<%@ Page Title="HalkOde" Language="C#" MasterPageFile="~/Site.Master" Async="true" AutoEventWireup="true" CodeBehind="GetPos.aspx.cs" Inherits="form.GetPos" %>


<asp:Content ID="BodyContent" ContentPlaceHolderID="MainContent" runat="server">
    <div class="container">
        <h2 class="text-center mt-4">Taksit Gösterme</h2>

        <div class="card p-4 mt-3 shadow">


            <label>Kart Numarası:</label>
            <asp:TextBox ID="cardNumber" CssClass="form-control" runat="server"></asp:TextBox>


            <label>Toplam Tutar (TRY):</label>
            <asp:TextBox ID="totalAmount" CssClass="form-control" runat="server"></asp:TextBox>


            <asp:Button ID="btnPay" CssClass="btn btn-primary mt-3 w-100" runat="server" Text="Ödeme Yap" OnClick="ProcessPayment_Click" />

            <div class="result mt-3">
                <asp:Label ID="lblResult" CssClass="alert alert-info d-block text-center" runat="server" Text=""></asp:Label>
            </div>

        </div>
    </div>
</asp:Content>
