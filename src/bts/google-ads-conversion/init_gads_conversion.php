<?php
function conditional_enqueue_google_ads(){
    $options = get_option( 'afbp_google_ads_conversions_option_name' );

    if(! isset($options['record_google_ads_conversions'])){ //make sure admin has activated conversions
        return;
    }

    $conversion_trigger_param = $options['param_to_watch'];
    $scriptName = 'logCustomGoogleAdsConversion';
    if( isset($_GET[$conversion_trigger_param]) ){
        wp_register_script($scriptName, plugin_dir_url( "./afb-parade/build/js" )."js/register-google-ads-conversion.js");
        wp_localize_script($scriptName, 'searchKeys', Array(
            'body' => utf8_uri_encode($options['body_json']),
        ));
        wp_enqueue_script($scriptName);
    }
}
?>