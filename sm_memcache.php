<?php
/**
 * {{{ smCache
 **/
class smCache { 
    private $_group_id;
    private $_servers;
    private $_memcache;
    private $_flag=0;

    /*** {{{  __construct 
    */ 
    public function __construct($group_id)
    {
        global $sm_config;
        $this->_group_id=$group_id;
        $this->_servers=$sm_config["memcache"][$group_id];
        $mem=new memcache();
        foreach($this->_servers as $server){
            $mem->addServer($server["host"],$server["port"]);
        }
    }
    /** }}} */
    /**
     * {{{ get_data
     * */
    function get_data($key){
        return $this->mem->get($key);
    }
    /**
     * }}}
     * */
    function set_data($key,$val,$expire=7200){
        return  $this->mem->set($key,$val,$this->_flag,$expire);
    }
    function set_flag($flag){
        $this->_flag = $flag;
    }
}
/**
 * }}}
 * */
