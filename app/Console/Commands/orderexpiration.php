<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Carbon\Carbon;
use App\Models\Orderdetail;

class orderexpiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'order expire description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
      /*  $orders = Order::where('paid', 1)->where('status', 'pending')
                                        ->where('status', '!=', 'cancelled')
                                        ->where('status', '!=', 'in progress')
                                        ->where('expired', 0)->get();
        foreach ($orders as $order) {
            $created_at = $order->created_at;
            $updated_at = $order->updated_at;
            $now = Carbon::now();
            $diff_created  = $now->diffInHours($created_at);
            $diff_updated  = $now->diffInHours($updated_at);
            if ($diff_created >= 48 && $diff_updated >= 48) {
                $order->update(['expired' => 1]);
                $order->update(['status' => 'cancelled']);
            }
        } */

        $orders = Order::where('paid', 1)->get();
        foreach ($orders as $order) {
            $orderDetails  = $order->orderDetails;
            $checkout_time = $order->checkout_time;
            $now = Carbon::now();
            $diff_created  = $now->diffInHours($checkout_time);
            
            if ($diff_created >= 48) {
                Orderdetail::whereIn('order_id', [$order->id])->where('approved', 0)->update([
                    'approved'   => 3,
                ]);
              //  $order->update(['expired' => 1]);
              //  $order->update(['status' => 'cancelled']);
            }
        }

        $this->info('orders expiry date revised successfully');
    }
}
