<?php


namespace Customer\Queues;


use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class PTApiLoginEvent implements ShouldQueue
{

    private $user_id;

    /**
     * PTApiLoginEvent constructor.
     * @param $user_id
     */
    public function __construct($user_id)
    {
        $this->user_id = $user_id;
    }

    public function handle()
    {
        DB::table('ec_users')->update(['latest_active' => Carbon::now()])->where('id', $this->user_id);
    }

}
