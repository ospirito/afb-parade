<?php
function conditional_query_param_callback($block_attributes, $content){
    if(array_key_exists("queryParam", $block_attributes)){
        $queryParam = $block_attributes["queryParam"];
        if(array_key_exists($queryParam, $_GET)){
            return $content;
        }
    }
}
?>