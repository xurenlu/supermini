<?php
class users extends smApplication {
    var $before_filters_new=array("establish_connect");
    function action_new(){
        $user=new smTable("test_user","id");
        $_GET["page"]=2;
        pr($user->page_by());
    }

}