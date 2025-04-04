$("#payment_halkode_card").on("change", function () {
    selectedText = $(this).find("option:selected").text();
    selectedVal = $(this).find("option:selected").val();
    if( selectedVal == "0" ){
        $('.cardForm input[type="text"]').prop('required', true);
        $(".cardForm").show();
        $('#deleteMyCard').hide();
    }else{
        $(".cardForm").hide();
        $('.cardForm input[type="text"]').prop('required', false);
        getInstallmentsByCard( selectedText );
        $('#deleteMyCard').show();
        let deletehref = $('#deleteMyCard').data('url');
        deletehref = deletehref + '&card_token=' + selectedVal;
        $('#deleteMyCard').attr('href', deletehref);
    }
});

$("#payment_halkode_taksit").on("change", function(){
    $("#payment_halkode_total").val( $(this).find(':selected').data('total') );
});

function getInstallment(val) {
    var len = val.value.length;
    if (len == 7 || len == 16) {
        getInstallmentsByCard(val.value)
    }
};

function getInstallmentsByCard(cardN) {

    $.ajax("index.php?route=extension/payment/halkodepayment/ajax&getInstallments=1", {
        type: 'POST',
        data: {card: cardN},
        //contentType: "application/json",
        dataType: "json",
        success: function (data, status, xhr) {
            $("#payment_halkode_taksit option").remove();
            $.each(data.taksitler, function (key, value) {

                if(key == 1)
                    $("#payment_halkode_total").val( value.toplam );

                $('#payment_halkode_taksit').append(
                    $("<option></option>").attr("value", key).text(value.text).attr("data-total", value.toplam)
                );
            });
        },
        error: function (jqXhr, textStatus, errorMessage) {
            alert('Error' + errorMessage);
        }
    });
}


