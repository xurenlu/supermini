<?php
$dir=dirname(__FILE__)."/";
$root=dirname(dirname(__FILE__))."/";
include $root."lib/sm.php";
include $root."sm_config.php";
include $dir."assert.php";
function test_form(){
    global $sm;
    assert_more() && assert($sm->form->html("input")=="<input />") && assert_no_error();
    assert_more() && assert(
        '<select id="user_user" name="user[user]" ><option value="1" >China</option><option value="2" >US</option></select>' == $sm->form->form("user",$user)->selectbox("user",array(array(1,"China"),array(2,"US")))
    )
    && assert_no_error();
    assert_more() && assert(
        '<select id="user_user" name="user[user]" ><option value="1" >China</option><option value="2" >US</option></select>' == $sm->form->selectbox("user",array(array(1,"China"),array(2,"US")))
    ) && assert_no_error();
    assert_more() && assert(
            '<img src="logo.jpg" />' == $sm->form->src("logo.jpg")->html("img")
        ) && assert_no_error();
}
function test_db(){
    global $sm;
    $sql="create table users (id bigint(11) auto_increment,username varchar(32) NULL ,email varchar(32) NULL,primary key (id))";
    assert_more() && assert($sm->db->query($sql)) && assert_no_error();
    $array=array("username"=>"xurenlu","email"=>"xurenlu@Gmail.com");
    assert_more() && assert($sm->db->table("users")->values($array)->insert()) && assert_no_error();
    assert_more() && assert( $sm->db->insert_id()==1) && assert_no_error();
    $row=$sm->db->table("users")->row();
    assert_more() && assert($row==array("id"=>1,"username"=>"xurenlu","email"=>"xurenlu@Gmail.com")) && assert_no_error();
    $array=array("username"=>"hello","email"=>"hello@Gmail.com");
    assert_more() && assert($sm->db->table("users")->values($array)->insert()) && assert_no_error();
    assert_more() && assert( $sm->db->insert_id()==2) && assert_no_error();
    assert_more() && assert($sm->db->table("users")->where("id>1")->count()==1) && assert_no_error();
    assert_more() && assert($sm->db->table("users")->count()==2) && assert_no_error();
    assert_more() && assert($sm->db->table("users")->where("id=1")->delete()) && assert_no_error();
    assert_more() && assert($sm->db->table("users")->count()==1) && assert_no_error();
    assert_more() && assert($sm->db->affected_rows()==1) && assert_no_error();
    assert_more() && assert($sm->db->query("drop table users")) && assert_no_error();
}
function test_urlmap(){
    global $sm,$sm_config,$sm_temp;
    $_SERVER["PHP_SELF"]="/index.php/main/test/";
    sm_open_shorturl();
    assert_more() && assert($_GET==array("controller"=>"main","action"=>"test")) && assert_no_error;
    assert_more() && assert("/index.php/user/login?type=yahoo"==sm_url(array("controller"=>"user","action"=>'login',"type"=>'yahoo'))) && assert_no_error;
}
    global $sm_config;
$sm_config["url_routes"]=
    array(
        "{controller}/{action}/pagename_{pagename}/{id}",
        "{controller}/{action}/w_{word}/p_{page}",
        "{controller}/{action}/w_{word}",
        "{controller}/{action}/sec_{section_id}/ch_{channel_id}",
        "{controller}/{action}/sec_{section_id}",
        "{controller}/{action}/ch_{channel_id}/p_{page}",
        "{controller}/{action}/ch_{channel_id}",
        "{controller}/{action}/p_{page}\.{format}",
        "{controller}/{action}/p_{page}",
        "{controller}/{action}/{id}-p-{page}\.{format}",
        "{controller}/{action}/md-{module_id}-{id}\.{format}",
        "{controller}/{action}/md-{module_id}-{id}-p-{page}",
        "{controller}/{action}/md-{module_id}-{id}",
        "{controller}/{action}/{id}\.{format}",
        "{controller}/{action}/{id}",
        "{controller}/{action}\.{format}",
        "{controller}/{action}",
        "{id}\.{format}",
        "{file}"
    );
$sm_config["url_maps"]=
    array(
        'v-' => 'questionfront/view/',
        'show-' => 'articlefront/view/pagename_article_view/',
        'c-' => 'commentfront/view/'
    );  
$_SERVER["PHP_SELF"]="/index.php/main/test";
$sm_config["url_namespace"]="/index.php/";
error_reporting(E_ALL&~E_WARNING&~E_NOTICE);
$sm_config["sql_debug"]=false;
$sm_config["mysql"][0]=array("host"=>"localhost","user"=>"root","password"=>"","database"=>"test");
$sm_config["mysql"][1]=array("host"=>"localhost","user"=>"root","password"=>"","database"=>"test");
run_test();
