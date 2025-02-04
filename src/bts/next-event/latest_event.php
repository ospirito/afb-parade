<?php
function renderLatest(){
    if(preg_match("/\/latest$/i", $_SERVER['REQUEST_URI'])){
        header("Location: http://www.example.com/");
        exit();
    }
    
}

?>