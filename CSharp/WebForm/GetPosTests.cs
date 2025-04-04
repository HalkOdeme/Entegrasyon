using System;
using System.Net;
using System.Net.Http;
using System.Text;
using System.Threading.Tasks;
using Moq;
using Newtonsoft.Json;
using NUnit.Framework;

namespace form.Tests
{
    [TestFixture]
    public class GetPosTests
    {
        private Mock<IHttpClientWrapper> _httpClientMock;
        private GetPos _getPos;

        [SetUp]
        public void SetUp()
        {
            _httpClientMock = new Mock<IHttpClientWrapper>();
            _getPos = new GetPos(_httpClientMock.Object);
        }

        [Test]
        public async Task GetToken_ShouldReturnToken_WhenResponseIsSuccessful()
        {
            // Arrange
            var tokenResponse = new { status_code = 100, data = new { token = "test_token" } };
            var responseMessage = new HttpResponseMessage(HttpStatusCode.OK)
            {
                Content = new StringContent(JsonConvert.SerializeObject(tokenResponse), Encoding.UTF8, "application/json")
            };
            _httpClientMock.Setup(x => x.PostAsync(It.IsAny<string>(), It.IsAny<HttpContent>())).ReturnsAsync(responseMessage);

            // Act
            var token = await _getPos.GetToken();

            // Assert
            Assert.AreEqual("test_token", token);
        }

        [Test]
        public async Task GetToken_ShouldReturnNull_WhenResponseIsUnsuccessful()
        {
            // Arrange
            var tokenResponse = new { status_code = 200 };
            var responseMessage = new HttpResponseMessage(HttpStatusCode.OK)
            {
                Content = new StringContent(JsonConvert.SerializeObject(tokenResponse), Encoding.UTF8, "application/json")
            };
            _httpClientMock.Setup(x => x.PostAsync(It.IsAny<string>(), It.IsAny<HttpContent>())).ReturnsAsync(responseMessage);

            // Act
            var token = await _getPos.GetToken();

            // Assert
            Assert.IsNull(token);
        }

        [Test]
        public async Task ProcessPaymentAsync_ShouldSetLblResult_WhenTokenIsNull()
        {
            // Arrange
            _httpClientMock.Setup(x => x.PostAsync(It.IsAny<string>(), It.IsAny<HttpContent>())).ReturnsAsync(new HttpResponseMessage(HttpStatusCode.OK)
            {
                Content = new StringContent(JsonConvert.SerializeObject(new { status_code = 200 }), Encoding.UTF8, "application/json")
            });

            // Act
            await _getPos.ProcessPaymentAsync();

            // Assert
            Assert.IsTrue(_getPos.lblResult.Text.Contains("Token alýnamadý!"));
        }

        [Test]
        public async Task ProcessPaymentAsync_ShouldSetLblResult_WhenTotalAmountIsInvalid()
        {
            // Arrange
            _getPos.totalAmount.Text = "-1";

            // Act
            await _getPos.ProcessPaymentAsync();

            // Assert
            Assert.IsTrue(_getPos.lblResult.Text.Contains("Geçersiz toplam tutar!"));
        }

        [Test]
        public async Task ProcessPaymentAsync_ShouldSetLblResult_WhenApiResponseIsUnsuccessful()
        {
            // Arrange
            var tokenResponse = new { status_code = 100, data = new { token = "test_token" } };
            var responseMessage = new HttpResponseMessage(HttpStatusCode.OK)
            {
                Content = new StringContent(JsonConvert.SerializeObject(tokenResponse), Encoding.UTF8, "application/json")
            };
            _httpClientMock.Setup(x => x.PostAsync(It.IsAny<string>(), It.IsAny<HttpContent>())).ReturnsAsync(responseMessage);

            var apiResponse = new HttpResponseMessage(HttpStatusCode.BadRequest)
            {
                Content = new StringContent("Bad Request", Encoding.UTF8, "application/json")
            };
            _httpClientMock.Setup(x => x.PostAsync("https://testapp.halkode.com.tr/ccpayment/api/getpos", It.IsAny<HttpContent>())).ReturnsAsync(apiResponse);

            // Act
            await _getPos.ProcessPaymentAsync();

            // Assert
            Assert.IsTrue(_getPos.lblResult.Text.Contains("Hata Kodu:"));
            Assert.IsTrue(_getPos.lblResult.Text.Contains("Hata Mesajý:"));
        }
    }
}
