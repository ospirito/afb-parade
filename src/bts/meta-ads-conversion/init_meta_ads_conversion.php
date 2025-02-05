<?php
function conditional_enqueue_meta_ads(){
    $options = get_option( 'afbp_meta_ads_conversions_option_name' );
    //echo $options;
    if(! isset($options['record_meta_ads_conversions'])){ //make sure admin has activated conversions
        return;
    }

    $conversion_trigger_param = $options['param_to_watch'];
    $scriptName = 'logMetaAdsPurchaseConversion';
    if( isset($_GET[$conversion_trigger_param]) ){
        wp_register_script($scriptName, plugin_dir_url( "./afb-parade/build/js" )."js/register-meta-ads-conversion.js");
        wp_localize_script($scriptName, 'settingsPayload', Array(
            'body' => utf8_uri_encode($options['body_json']),
        ));
        wp_enqueue_script($scriptName);
    }
}
?>