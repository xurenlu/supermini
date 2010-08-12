<?php
/*** {{{  sm_log 
 * 日志记录;
*/ 
function sm_log($msg)
{
    error_log($msg); 
}
/** }}} */

/*** {{{  sm_gen_url 
 */ 
function sm_gen_url($string,$url_pattern,$get_args=array())
{
    $targetURL=$url_pattern;
    foreach($get_args as $k=>$v)
    {
        $targetURL=str_replace("{".$k."}",$v,$targetURL);
    }
    if(strlen($targetURL)>0)	return  $targetURL;
    else return $string;
}
/** }}} */
/**
 * @author xurenlu <helloasp@hotmail.com>
 * 探测一个变量是否已经被urlencode过了。
 */
function test_urlencode($var)
{
    if(urldecode($var)==$var)
        return false;
    else
        return true;
}
/**
 * Page navgation ，
 * 
 * @author xurenlu <helloasp@hotmail.com>
 * @link http://www.162cm.com
 * @copyright All wroten myself.You can use it free,But don't remove the copyright.
 * @param int $total 总记录个数
 * @param int $pagesize 每页记录数
 * @param string $pagestr 其他分页的链接模板
 * @param array $get	一般情况下就是GET数组
 * @param string $page_var_name 一般是page
 * @param int $l 	当前页链接的左边保留多少个链接
 * @param int $r 	当前页链接的右边保留多少个链接
 * @param int $jump 是否加跳转表单。但是当前只有一页时，不显示此跳转表单。
 * @example
 * echo sm_pagenav_default(18244,25,null,null,"page",3,3);
 * echo sm_pagenav_default(18332,20);
 * echo sm_pagenav_default(18244,25,"index.php?page={page}",array("key"=>1),"page",3,3);
 * echo sm_pagenav_default(18244,25,null,array("key"=>1),"page",3,3);
 */

function sm_pagenav_default($total,$pagesize=null,$pagestr=null,$get_args=null,$page_var_name="page",$l=4,$r=4,$jump=true)
{
    global $sm_temp;
    $url_pattern=$sm_temp["url_pattern"];
    if(is_null($pagestr))
    {
        $arr=array();
        if(is_null($get_args))
            $get_args=$_GET;


        while(list($key,$val)=each($get_args)){
            if(!test_urlencode($val))
                $val=urlencode($val);

            if(strtolower($key)!=$page_var_name)
                $arr[]=$key."=".$val;
        }
        $arr[]=$page_var_name."={page}";
        $pagestr="?".join("&",$arr);
    }
    $get_args[$page_var_name]="{page}";
    if(is_null($pagesize))
    {
        global $sm_config;
        $pagesize=$sm_config["pagesize"]>0?$sm_config["pagesize"]:20;
    }
    $pagecount=$total/$pagesize;
    if(floor($pagecount)<$pagecount)
        $pagecount= floor($pagecount)+1;


    if(! ($_GET[$page_var_name]>0))
    {
        $_GET[$page_var_name]=1;
        $pagenow=1;
    }
    else
        $pagenow=$_GET[$page_var_name];


    $sn="page_".rand(1000,9999);
    $str="<form  onsubmit='javascript:return false;'>一共".$pagecount."页，".$total."个记录。当前为第".$pagenow."页。";
    if ($pagenow>1){
        $str=$str."&nbsp;&nbsp;<a href='".sm_gen_url(str_replace("{page}","1",$pagestr),str_replace("{page}",1,$url_pattern),$get_args)."'>|<<</a>&nbsp;";
        $str =$str." <a href='".sm_gen_url(str_replace("{page}",($pagenow-1),$pagestr),str_replace("{page}",($pagenow-1),$url_pattern),$get_args)."'><</a>&nbsp;";
    }
    $startpage=$pagenow-$l;
    $endpage=$pagenow+$r;
    if($startpage<2) $startpage=2;
    if($endpage>=$pagecount) $endpage=$pagecount;
    for($jj=$startpage;$jj<=$endpage;$jj++)
    {
        if($jj==$pagenow)
            $str=$str."<strong>".$jj."</strong>&nbsp;";
        else
            $str=$str."<a href='".sm_gen_url(str_replace("{page}",$jj,$pagestr),str_replace("{page}",$jj,$url_pattern),$get_args)."'>".$jj."</a>&nbsp;";

        //echo "<font color='red'>".$url_pattern."</font><br/>";
        //	echo sm_gen_url(str_replace("{page}",$jj,$pagestr),$url_pattern);
    }
    if($pagenow<$pagecount)
    {
        $str=$str."<a href='".sm_gen_url(str_replace("{page}",$pagenow+1,$pagestr),str_replace("{page}",$pagenow+1,$url_pattern),$get_args)."'>&gt;</a>&nbsp;";
        $str=$str."<a href='".sm_gen_url(str_replace("{page}",$pagecount,$pagestr),str_replace("{page}",$pagecount,$url_pattern),$get_args)."'>&gt;&gt;</a>&nbsp;";
    }
    if($pagecount>1)
        if($jump)
        {
            $str=$str."跳到<input type=\"text\" name=\"txtpage\" id='input_".$sn."' size=\"3\" class=\"tinput\" / >页";
            $str=$str."<input type=\"button\" value=\"GO\" class=\"tinput\"
                onclick=\"javascript:if((document.getElementById('input_".$sn."').value>=1) &&(document.getElementById('input_".$sn."').value<=".$pagecount.") &&(document.getElementById('input_".$sn."').value!=".$pagenow.")) window.location='".sm_gen_url($pagestr,$url_pattern,$get_args)."'.replace('{page}',document.getElementById('input_".$sn."').value);\"/></form>";
        }
    //	echo "<hr/><font color='red'>".sm_gen_url($pagestr,$url_pattern)."</font>";
    return $str;
}
