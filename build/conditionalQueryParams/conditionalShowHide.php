<?php
function conditional_query_param_callback($block_attributes, $content){
    if(!array_key_exists("matchType", $block_attributes)){
        return;
    }
    $mode = $block_attributes["matchType"];

    #filter out any preview params
    $params = array_filter($_GET, function($key){ return !str_contains('preview', $key) && !str_contains('page_id', $key); }, ARRAY_FILTER_USE_KEY);
    $isPreviewMode = is_preview();
    
    # if the mode is "no params" and we don't have any non-preview params, just return the content
    if($mode == "noParams" && count($params) == 0){
        $warning = "<div class='parade-warn'>You're in preview mode! Because of this, we're ignoring the preview and page_id query params. This message won't show in production, but this block will of there aren't any query params.</div>";
        $rendered_content = $isPreviewMode ? $warning . $content : $content;
       return $rendered_content;
    }

    # check to see if the queryParam exists inside of the attributes
    if(array_key_exists("queryParam", $block_attributes)){
        $queryParam = $block_attributes["queryParam"];

        # see if the specified queryParam entered by the user exists in the URL params
        if(array_key_exists($queryParam, $params)) { 
            # if the mode is param and value, check to make sure an exactValue is defined by the user
            if($mode == "paramAndValue" && array_key_exists("exactValue",$block_attributes)){
                $exactValue = $block_attributes["exactValue"];
                # see if the specified value is the value of the queryParam 
                if($params[$queryParam] == $exactValue){
                    return $content;
                }
            # otherwise, if the paramOnly mode is selected, we know that the param exists and can just pass back the content
            }elseif($mode == "paramOnly"){
                return $content;
            }
        }
    }
# if no conditons are met, return an empty string
    return "";
}
?>