<?php
class users extends smApplication {
    var $before_filters_new=array("establish_connect");
    function action_new(){
        global $sm,$sm_data,$sm_temp;
        $sm_data["users"]=$sm->db->table("users")->page();
        $this->v();
    }

}
