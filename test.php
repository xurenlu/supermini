<?php
include "./sm.php";
include "./sm_config.php";

$conn = sm_dbo(0);
$user= new smTable("test_user","id",$conn);
/** testing code */

/**
$user= new smTable("users","id");
$user->find_by_user_id_and_name_and_email("user_id_8","xurenlu","renlu.xu@gmail.com",array("limit"=>"10,20",
    "where"=>" category=3","group_by"=>" category","order_by"=>"id desc" ,"per_page"=>20,"page"=>1));
$user->page_by_user_id_and_name_and_email("user_id_8","xurenlu","renlu.xu@gmail.com",array("where"=>" category=3","group_by"=>" category","order_by"=>"id desc" ,"per_page"=>20,"page"=>2));
 */
/**
$row = $user->find_by("name='kiwiphp'");
print_r($row);
 */
$row = $user->page_by();
print_r($row);
exit();
$row = $user->find_row_by("name='kiwiphp'");
print_r($row);
$row = $user->update_by("name='kiwiphp'",array("age"=>8));
print_r($row);
$row = $user->find_by("name='kiwiphp'");
print_r($row);
    $row=array("name"=>"wow".$i,"age"=>(19+$i));
    $user->create($row);
