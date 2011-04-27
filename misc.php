/**
 * {{{ _getip 得到用户ip;
 * */
function getip(){
    if(isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
        $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    }   
    elseif(isset($_SERVER["HTTP_CLIENT_IP"])){
        $realip = $_SERVER["HTTP_CLIENT_IP"];
    }   
    else{
        $realip = $_SERVER["REMOTE_ADDR"];
    }   
    return $realip; 
}
/*** }}} */
