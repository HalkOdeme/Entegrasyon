<%@ Page Title="Success" Language="C#" MasterPageFile="~/Site.Master" Async="true" AutoEventWireup="true" CodeBehind="Success.aspx.cs" Inherits="form.Success" EnableEventValidation="false" %>

<asp:Content ID="BodyContent" ContentPlaceHolderID="MainContent" runat="server">
    <div class="container">
        <h2 class="text-center mt-4">Ödeme Başarılı</h2>

        <div class="card p-4 mt-3 shadow">
            <asp:Label runat="server" Text="Kart Numarası:" CssClass="form-label"></asp:Label>
            <asp:Label ID="lblCardNumber" CssClass="form-control" runat="server"></asp:Label>

            <asp:Label runat="server" Text="Fatura No:" CssClass="form-label"></asp:Label>
            <asp:Label ID="lblInvoiceId" CssClass="form-control" runat="server"></asp:Label>

            <asp:Label runat="server" Text="Durum Kodu:" CssClass="form-label"></asp:Label>
            <asp:Label ID="lblStatusCode" CssClass="form-control" runat="server"></asp:Label>

            <asp:Label runat="server" Text="Durum Açıklaması:" CssClass="form-label"></asp:Label>
            <asp:Label ID="lblStatusDescription" CssClass="form-control" runat="server"></asp:Label>
        </div>

        <div id="apiResponseContainer" runat="server"></div>
    </div>
</asp:Content>

