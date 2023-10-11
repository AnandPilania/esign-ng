<?php


namespace Admin\Queues;


use Carbon\Carbon;
use Core\Models\eCAdmin;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class LoginEventQueue implements ShouldQueue
{
    private $admin_id;

    /**
     * LoginEventQueue constructor.
     * @param $admin_id
     */
    public function __construct($admin_id)
    {
        $this->admin_id = $admin_id;
    }


    public function handle()
    {
        eCAdmin::find($this->admin_id)->update(['latest_active' => Carbon::now()]);
    }
}
