<?php
header("Content-type:text/html;charset=utf8");
include "./lib/sm.php";
include "./sm_config.php";
$conn = sm_dbo(0);
$user= new smTable("test_user","id",$conn);
$auser= $user->find_row_by();
pr($auser);
//print_R($auser);
$form = new smForm("user",$auser);
//echo $form->begin("/user/new",array("method"=>"POST","class"=>"new_form"));
echo $form->begin("?act=go");//,array("method"=>"POST","class"=>"new_form"));
//echo $form->textarea("name",array("rows"=>2,"length"=>28));
echo $form->text_field("name",array("rows"=>2,"length"=>28));
//echo $form->textarea("age",array("rows"=>2,"length"=>28));
echo $form->select("age",array(array("0","me"),array("1","you"),array("8","old")),array("rows"=>2,"length"=>28));
echo $form->submit();
echo $form->end();
pr($form->fetch());
// echo "</form>";
//run_sm("users","new");
exit();
/**
$pathinfo="/question/108382.html";
$sm_config["url_rewrites"]=array(
    "default"=>
    array(
        "url_pattern"=>"/{question}/{id}.{format}",
        "question_default"=>"que
        exit();
 */
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
