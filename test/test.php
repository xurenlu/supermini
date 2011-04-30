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
error_reporting(E_ALL&~E_WARNING&~E_NOTICE);
run_test();
