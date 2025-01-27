<?php
function conditional_enqueue(){
    $options = get_option( 'afbp_google_ads_conversions_option_name' );

    if(! isset($options['record_google_ads_conversions'])){ //make sure admin has activated conversions
        return;
    }

    $conversion_trigger_param = $options['param_to_watch'];
    $scriptName = 'logCustomGoogleAdsConversion';
    $jsonString = '{"value":"~tt_value", "eventId":"~tt_event_id", "id":"~tt_order_id", "adId":"12345"}';
    //$conversionQueryParams = json_decode($jsonString);
    if( isset($_GET[$conversion_trigger_param]) ){
        wp_register_script($scriptName, plugin_dir_url( "./afb-parade/build/js" )."js/register-google-ads-conversion.js");
        wp_localize_script($scriptName, 'searchKeys', Array(
            'adsId' => $options['google_ads_id'],
            'value' => $options['param_conversion_value'],
            'id' => $options['param_conversion_unique_id'],
            'eventId' => $options['param_event_id'],
            'body' => utf8_uri_encode($options['body_json']),
        ));
        wp_enqueue_script($scriptName);
    }
}
?>