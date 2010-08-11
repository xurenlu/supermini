<?php
/**
* super mini 是
*/
function get_mem(){
    global $g_mem;
    if(is_object($g_mem)){
        return $g_mem;  
    }
    else{
        $g_mem=new memcache();
        $g_mem->addServer("ksw1.portal.cnb.yahoo.com","11211");
        $g_mem->addServer("ksw2.portal.cnb.yahoo.com","11211");
        return $g_mem;
    }
}

function get_mem_data($key){
    get_mem();
    global $g_mem;
    return $g_mem->get($key);
}

function set_mem_data($key,$val,$expire=7200){
    get_mem();
    global $g_mem;
    return  $g_mem->set($key,$val,0,$expire);
}
/**
$g_mem=get_mem();
set_mem_data("key1","valueaab");
var_dump(get_mem_data("key1"));

*/

