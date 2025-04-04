jQuery(document).ready(function(){

   jQuery(document).on('input paste cut blur dblclick', '#cc_number',function (e) {

	  



    jQuery(this).on('dblclick',function (e) {

        jQuery(this).val('');

       });



       var thisValue = jQuery(this).val();

       if (thisValue.length < 6){

        jQuery("#installments").html('');

       } else {

           var installmentArea = jQuery("#installments").html();

           if( installmentArea === '') {

               var formData = jQuery("#wc-halkode_sanalpos-cc-form").serialize();

               var token = jQuery('#halkode_token').val();

               ajaxRequest(woocommerce_params.ajax_url, formData, token);

           }

       }



   })

jQuery(document).on('click', '.stored_card', function(){
    if(jQuery(this).val() == 1){
        jQuery('.payment-form').hide();
        jQuery('.saved_card').show();
    } else {
        jQuery('.payment-form').show();
        jQuery('.saved_card').hide();
    }
    
})

jQuery(document).on('click', '#button-delete', function(){
    jQuery.ajax({
        url: woocommerce_params.ajax_url + '?action=delete_halkode_card',
        type: 'POST',
        data: {card:jQuery('#input-card-choice').val()},
        success: function (data, textStatus, jQxhr) {
            location.reload()

        }
    })
    
})

jQuery(document).on('keyup', '.alpha-only', function(){
    jQuery('.alpha-only').bind('keyup blur',function(){ 
        var node = jQuery(this);
        node.val(node.val().replace(/[^a-zA-Z' 'wığüşöçĞÜŞÖÇİ]/g,'') ); }   // (/[^a-z]/g,''
    );
})




   jQuery(document).on('click', ".single-installment", function(){

       jQuery(".pos_id").val(jQuery(this).attr('data-posid'));

       jQuery(".pos_amount").val(jQuery(this).attr('data-amount'));

       jQuery(".currency_id").val(jQuery(this).attr('data-currency_id'));

       jQuery(".campaign_id").val(jQuery(this).attr('data-campaign_id'));

       jQuery(".currency_code").val(jQuery(this).attr('data-currency_code'));

       jQuery(".allocation_id").val(jQuery(this).attr('data-allocation_id'));

       jQuery(".installments_number").val(jQuery(this).attr('data-installments_number'));

       jQuery(".hash_key").val(jQuery(this).attr('data-hash_key'));

       jQuery('.single-installment').removeClass('active');

       jQuery(this).addClass('active');

   });



   jQuery('form.checkout').on('click', 'input[name="payment_method"]', function(){

        if(jQuery(this).val() == "halkode_payment"){

            jQuery("#place_order").addClass('halkode_place_order');

        }else{

            jQuery("#place_order").removeClass('halkode_place_order');

        }

   });



  /* jQuery('form.checkout').on('click', 'button.halkode_place_order', function(e){

       if(jQuery('#halkode_3d').val() == 1){

            e.preventDefault();

            alert('hey');

            jQuery('body').append('<div class="halkode_3d_popup"><form action="http://wpdemo.learnetech.com/cart" method="post" class="halkode_3d_form"><input type="text" name="fname" value="Test"></form></div>');

            jQuery('.halkode_3d_form').submit();

       }

   });*/



   jQuery(document).on('click', '#recurring_checkbox', function(){

       if(jQuery(this).is(':checked'))

        jQuery('.recurring_option_fields').show();

        else   

        jQuery('.recurring_option_fields').hide();

   });



});

function ajaxRequest(url,formData, token) {



    var spinner = '<img src="'+halkode_var.spinner+'" class="halkode_spinner"/>';

    //jQuery(spinner).insertAfter(jQuery('#cc_number'));

    jQuery('.halkode_spinner_blk').html(spinner);



    jQuery.ajax({

        url: url+"?action=get_installment",

        type: 'post',

        data: formData+"&token="+token,

        dataType: "JSON",

        success: function (data, textStatus, jQxhr) {

            jQuery("#installments").html(data.data);

            jQuery(".pos_id").val(data.pos_id);

            jQuery(".pos_amount").val(data.pos_amt);

            jQuery(".currency_id").val(data.currency_id);

            jQuery(".campaign_id").val(data.campaign_id);

            jQuery(".currency_code").val(data.currency_code);

            jQuery(".allocation_id").val(data.allocation_id);

            jQuery(".installments_number").val(data.installments_number);
			
            jQuery(".hash_key").val(data.hash_key);

            jQuery(".halkode_spinner").remove();

        },

        error: function (jqXhr, textStatus, errorThrown) {

            console.log(errorThrown);

            jQuery(".halkode_spinner").remove();

        }

    });

}