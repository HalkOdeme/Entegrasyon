


<%@ Page Title="Success" Language="C#" MasterPageFile="~/Site.Master" Async="true" AutoEventWireup="true" CodeBehind="Fail.aspx.cs" Inherits="form.Fail" EnableEventValidation="false" %>

<asp:Content ID="BodyContent" ContentPlaceHolderID="MainContent" runat="server">
    <div class="container">
        <h2 class="text-center mt-4">Ödeme Başarısız</h2>

        <div class="card p-4 mt-3 shadow">
            <label><b>Hata Kodu:</b></label>
            <asp:Label ID="lblErrorCode" CssClass="form-control" runat="server"></asp:Label>

            <label><b>Hata Açıklaması:</b></label>
            <asp:Label ID="lblErrorMessage" CssClass="form-control" runat="server"></asp:Label>

            <label><b>Durum Açıklaması:</b></label>
            <asp:Label ID="lblStatusDescription" CssClass="form-control" runat="server"></asp:Label>
        </div>
    </div>
</asp:Content>