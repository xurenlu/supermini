<?php
include "../lib/sm.php";
include "../sm_config.php";
include "./assert.php";
function test_form(){
    global $sm;
    assert_more() && assert($sm->form->html("input")=="<input />") && assert_no_error();
    assert_more() && assert(
        '<select id="user_user" name="user[user]" ><option value="1" >China</option><option value="2" >US</option></select>' == $sm->form->form("user",$user)->selectbox("user",array(array(1,"China"),array(2,"US")))
    )
    && assert_no_error();
    assert_more() && assert(
        '<select id="_user" name="[user]" ><option value="1" >China</option><option value="2" >US</option></select>' == $sm->form->selectbox("user",array(array(1,"China"),array(2,"US")))
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
error_reporting(E_ALL&~E_WARNING&~E_NOTICE);
//$sm_config["sql_debug"]=false;
$sm_config["mysql"][0]=array("host"=>"localhost","user"=>"root","password"=>"","database"=>"test");
$sm_config["mysql"][1]=array("host"=>"localhost","user"=>"root","password"=>"","database"=>"test");
run_test();
