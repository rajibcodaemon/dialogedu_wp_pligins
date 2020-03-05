jQuery(document).ready(function() {
    jQuery("#pull").click(function () {
        var site_url = jQuery('#pull').attr('data-site-url');
        jQuery('#loader').css('visibility', 'visible');
        try{
            jQuery.get( site_url + "/wp-json/lms/v1/courses", function( data ) {
                jQuery('#loader').css('visibility', 'hidden');
                jQuery('#lms-pull-course-result').css('visibility', 'visible');
                //console.log( data );
                jQuery('#inserted').html(data.count_insert);
                jQuery('#updated').html(data.count_update);
            }, 'json')
            .done(function() {
                jQuery('#loader').css('visibility', 'hidden');
                alert( "All data pulled successfully" );
            })
            .fail(function() {
                jQuery('#loader').css('visibility', 'hidden');
                alert( "There are some error pulling data. Please try again" );
            }).always(function() {
                jQuery('#loader').css('visibility', 'hidden');
            });
        }catch(e){
            jQuery('#loader').css('visibility', 'hidden');
            console.log( e );
        }
    });

    jQuery("#save_config_data").click(function () {
        var site_url = jQuery('#save_config_data').attr('data-site-url');

        var api_url = jQuery("#api_url").val();
        var authorization_url = jQuery("#authorization_url").val();
        var client_id = jQuery("#client_id").val();
        var redirect_url = jQuery("#redirect_url").val();
        var secret_id = jQuery("#secret_id").val();
        var token_url = jQuery("#token_url").val();
        var auth_code = jQuery("#auth_code").val();
        var access_token = jQuery("#access_token").val();
        var site_id = jQuery("#site_id").val();
        var err = 0;

        if(api_url ==''){
            jQuery("#api_url").css('border', '1px solid #f00');
            err++;
        }else{
            jQuery("#api_url").css('border', '');
        }

        if(authorization_url==''){
            jQuery("#authorization_url").css('border', '1px solid #f00');
            err++;
        }else{
            jQuery("#authorization_url").css('border', '');
        }

        if(client_id==''){
            jQuery("#client_id").css('border', '1px solid #f00');
            err++;
        }else{
            jQuery("#client_id").css('border', '');
        }

        if(redirect_url==''){
            jQuery("#redirect_url").css('border', '1px solid #f00');
            err++;
        }else{
            jQuery("#redirect_url").css('border', '');
        }

        if(secret_id==''){
            jQuery("#secret_id").css('border', '1px solid #f00');
            err++;
        }else{
            jQuery("#secret_id").css('border', '');
        }

        if(auth_code==''){
            jQuery("#auth_code").css('border', '1px solid #f00');
            err++;
        }else{
            jQuery("#auth_code").css('border', '');
        }

        if(token_url==''){
            jQuery("#token_url").css('border', '1px solid #f00');
            err++;
        }else{
            jQuery("#token_url").css('border', '');
        }

        if(access_token==''){
            jQuery("#access_token").css('border', '1px solid #f00');
            err++;
        }else{
            jQuery("#access_token").css('border', '');
        }

        if(site_id==''){
            jQuery("#site_id").css('border', '1px solid #f00');
            err++;
        }else{
            jQuery("#site_id").css('border', '');
        }

        if(err == 0){
            jQuery('#loader').css('visibility', 'visible');
            try{
                jQuery.post( site_url + "/wp-json/lms/v1/saveconfiguration", {'api_url':api_url, 'authorization_url':authorization_url, 'client_id':client_id, 'redirect_url':redirect_url, 'secret_id':secret_id, 'token_url':token_url, 'auth_code':auth_code, 'access_token':access_token, 'site_id':site_id}, function( data ) {
                    jQuery('#loader').css('visibility', 'hidden');
                    //console.log(data);
                })
                .done(function() {
                    location.reload();
                })
                .fail(function() {
                    alert( "There are some error saving data. Please try again" );
                });
            }catch(e){
                console.log( e );
            }
        }
    });

    jQuery("#get_auth_code").click(function () {
        var err = 0;
        var api_url = jQuery("#api_url").val();
        var authorization_url = jQuery("#authorization_url").val();
        var client_id = jQuery("#client_id").val();
        var redirect_url = jQuery("#redirect_url").val();

        if(api_url ==''){
            jQuery("#api_url").css('border', '1px solid #f00');
            err++;
        }else{
            jQuery("#api_url").css('border', '');
        }

        if(authorization_url==''){
            jQuery("#authorization_url").css('border', '1px solid #f00');
            err++;
        }else{
            jQuery("#authorization_url").css('border', '');
        }

        if(client_id==''){
            jQuery("#client_id").css('border', '1px solid #f00');
            err++;
        }else{
            jQuery("#client_id").css('border', '');
        }

        if(redirect_url==''){
            jQuery("#redirect_url").css('border', '1px solid #f00');
            err++;
        }else{
            jQuery("#redirect_url").css('border', '');
        }

        if(err == 0){
            var url = authorization_url + '?client_id=' + client_id + '&redirect_uri=' + redirect_url + '&response_type=code';
            window.open(url, '_blank');
        }
    });

    jQuery("#get_access_token").click(function () {
        var err = 0;
        var secret_id = jQuery("#secret_id").val();
        var auth_code = jQuery("#auth_code").val();
        var token_url = jQuery("#token_url").val();
        var api_url = jQuery("#api_url").val();

        var client_id = jQuery("#client_id").val();
        var redirect_url = jQuery("#redirect_url").val();

        var site_url = jQuery('#save_config_data').attr('data-site-url');

        if(secret_id==''){
            jQuery("#secret_id").css('border', '1px solid #f00');
            err++;
        }else{
            jQuery("#secret_id").css('border', '');
        }

        if(auth_code==''){
            jQuery("#auth_code").css('border', '1px solid #f00');
            err++;
        }else{
            jQuery("#auth_code").css('border', '');
        }

        if(token_url==''){
            jQuery("#token_url").css('border', '1px solid #f00');
            err++;
        }else{
            jQuery("#token_url").css('border', '');
        }

        if(err == 0){
            jQuery('#loader').css('visibility', 'visible');
            try{
                jQuery.post( site_url + "/wp-json/lms/v1/accesstoken", {'client_id':client_id, 'secret_id':secret_id, 'auth_code':auth_code, 'token_url':token_url, 'redirect_url':redirect_url, 'api_url':api_url}, function( data ) {
                    jQuery('#loader').css('visibility', 'hidden');
                    // console.log(data);
                    // console.log(data.error);
                    // console.log(data.access_token);
                    // console.log(data.site_id);
                    if(data.error == ''){
                        jQuery("#access_token").val(data.access_token);
                        jQuery("#site_id").val(data.site_id);
                    }else{
                        alert('There are some error generating new access token : '+data.error);
                    }
                }, 'json')
                .done(function() {
                    //alert( "All data pulled successfully" );
                })
                .fail(function() {
                   //alert( "There are some error pulling data. Please try again" );
                });
            }catch(e){
                console.log( e );
            }
        }
    });
});
