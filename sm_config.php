<?php
/** sm_config,sm_temp,sm_data 是整个框架依赖的三个全局变量,sm_config 用来设置全局配置;sm_temp是中间变量,做一些处理工作, sm_data 用来从controller中传递数据到viewer来处理输出;  */
global $sm_config,$sm_temp,$sm_data;
$sm_config["mysql"][0]=array("host"=>"localhost","user"=>"root","password"=>"","database"=>"tao");
$sm_config["mysql"][1]= $sm_config["mysql"][0];
$sm_config["mysql"][2]=array("host"=>"localhost","user"=>"root","password"=>"","database"=>"tutong");
$sm_config["mysql"][3]=array("host"=>"localhost","user"=>"root","password"=>"","database"=>"tutong");

$sm_config["mysql"]["cms"]=array("host"=>"localhost","user"=>"root","password"=>"","database"=>"codeanycms");
$sm_config["table_prefix"]="codeany_";
$sm_config["prepare_sql"]="set names utf8";
$sm_config["memcache"]["group_1"]=
    array(
        array("host"=>"localhost","port"=>11211),
        array("host"=>"localhost","port"=>11211)
    );
$sm_config["debug"]=true;
$sm_config["sql_debug"]=false;
$sm_config["pagesize"]=20;
$sm_config["app_root"]=dirname(dirname(__FILE__));
$sm_config["encrypt_secret"]='832-832phfdsf8y4idfjer[iuyutrsedhjery874knr9342[gof]f@$%YE$#^*U*(^U#^&GHGFU*(JGRUo{PIU*%$IKJH6734iu734khgfdsfghqewjfdttyiupojunfrkjgttuikojrrds345yjuj234578ijhgdee3yuio9632shj;,gt5dxfyhjikl,tedgvnkop';
$sm_config["url_routes"]=
    array(
        "{controller}/{action}/p_{page}\.{format}",
        "{controller}/{action}/p_{page}",
        "{controller}/{action}/{id}-p-{page}\.{format}",
        "{controller}/{action}/{id}\.{format}",
        "{controller}/{action}/{id}",
        "{controller}/{action}\.{format}",
        "{controller}/{action}",
        "{controller}",
    );
$sm_config["url_maps"]=
    array(
        'v-' => 'questionfront/view/',
        'show-' => 'articlefront/view/pagename_article_view/',
        'c-' => 'commentfront/view/'
    );  
$sm_config["url_namespace"]=($_SERVER["SCRIPT_NAME"])."/";
$sm_config["use_layout"]=true;
$sm_config["LC_ALL"]="zh_CN";
define("PREFIX",$sm_config["table_prefix"]);
