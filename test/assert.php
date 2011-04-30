<?php
/**
 * Assert settting
 * USAGE:assert_more()&&assert(...)&&assert_no_error();
 */
assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_WARNING, 1);
assert_options(ASSERT_QUIET_EVAL, 1);
//Create a handler function
function my_assert_handler($file, $line, $code)
{
        global $assert_errors;
        $assert_errors++;
        if(php_sapi_name()=="cli")
        {
                echo "\n\nAssertion Failed:
        File '$file'
        Line '$line'
        Code '$code'\n\n";
        }
        else
        {
                echo "<hr>Assertion Failed:
        File '$file'<br />
        Line '$line'<br />
        Code '$code'<br /><hr />";
        }
}
// Set up the callback
assert_options(ASSERT_CALLBACK, 'my_assert_handler');
function assert_more(){
	global $assert_times;
	$assert_times++;
	return true;
}
function assert_no_error(){
	global $assert_right_times;
	$assert_right_times++;
	return true;
}
function run_test()
{
        $functions=get_defined_functions();
        foreach($functions["user"] as $f)
        {
                if(preg_match("/^test_/i",$f))
                {
                        debug("now goto run unittest:$f\n");
                        $f();
                }
        }
		global $assert_times,$assert_right_times;
		if(php_sapi_name()=="cli"){
			echo "\nTotal:$assert_times asserts tested.\n";
			echo "$assert_right_times  asserts succeed!\n";
		}
		else{
			echo "\nTotal:$assert_times asserts tested.<br>\n";
			echo "$assert_right_times  asserts succeed!<br>\n";
		}
        global $assert_errors;
        if($assert_errors==0){
			if(php_sapi_name()=="cli"){
				echo "
					===================================================
							Congratulations !
							No assert_errors!
					===================================================\n"; 
			}else{
				echo "
					===================================================<br>
							Congratulations !<br>
							No assert_errors!<br>
					===================================================\n<br>"; 
			}	
		}
}
function debug($var)
{
        global $DEBUG;
        if($DEBUG){
                if(php_sapi_name()=="cli")
                        print_r($var);
                else
                        var_dump($var);
        }
        print "\n";
}
function mylog($msg)
{
        global $DEBUG;
        global $PRO;
        if($PRO)
                error_log($msg);
}
