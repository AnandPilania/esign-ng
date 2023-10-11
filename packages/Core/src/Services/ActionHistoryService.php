<?php


namespace Core\Services;


use Core\Helpers\Common;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Core\Models\eCActivities;
use Illuminate\Support\Facades\Auth;

class ActionHistoryService
{
    public function SetActivity($action_type,$user,$action_group,$data_table, $action, $note, $raw_log)
    {
        if($user->full_name){
            $user->name =  $user->full_name;
        }
        if($user->username){
            $user->name =  $user->username;
        }
        $active = new eCActivities();
        $active->created_by = $user->id;
        $active->company_id = $user->company_id;
        $active->action_group = $action_group;
        $active->action_type = $action_type;
        $active->data_table = $data_table;
        $active->action = $action;
        $active->note = $note;
        $active->name = $user->name;
        $active->email = $user->email;
        $active->raw_log = $raw_log;
        $active->save();
    }

}
