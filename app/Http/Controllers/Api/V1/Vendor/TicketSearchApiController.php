<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\Ticketcategory;
use App\Models\Ticketcomment;
use App\Http\Resources\Vendor\TicketsApiResource;
use App\Http\Resources\Vendor\ApiSpecificTicketResource;
use App\Models\AddVendor;
use App\Models\Order;
use Auth;
use Gate;
use App\Http\Requests\SearchApisRequest;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Vendorstaff;

class TicketSearchApiController extends Controller
{
  public function specific_order_tickets(Request $request, $id)
  {
      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();
      
        $order   = Order::where('id', $id)->where('user_id', $user->id)->first();
        if (!$order) {
          return response()->json([
                'status_code' => 400,
                'errors' => 'wrong order',
            ], 400);
        }
        $tickets = $order->tickets;
        $total   = count($tickets);
        $data = TicketsApiResource::collection($tickets);

            return response()->json([
                'status_code' => 200,
                'message' => 'success',
                'data' => $data,
                'total' => $total,
            ], 200);
  }
    // start search tickets with name
     public function search_with_name(SearchApisRequest $request)
     {
      //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'ASC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      
        $search_index = $request->search_index;
        $user         = Auth::user();
        $user_roles   = $user->roles->pluck('title')->toArray();

        // case admin search
        if (in_array('Admin', $user_roles)) {
            if ($ordered_by == 'order_number') {
            $get_tickets = Ticket::select('tickets.*')
                             ->join('orders', 'tickets.order_id', '=', 'orders.id')
                             //->join('add_vendors', 'invoices.vendor_id', '=', 'add_vendors.id')
                             ->where(function ($q) use ($search_index) {
                $q->where('ticket_no', 'like', "%{$search_index}%")
                  ->orWhere('title', 'like', "%{$search_index}%")
                  ->orWhere('priority', 'like', "%{$search_index}%")
                  ->orWhere('message', 'like', "%{$search_index}%")
                  ->orWhere('tickets.status', 'like', "%{$search_index}%");
                })
                ->orWhereHas('ticketVendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('ticketOrder', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%");
                                  //->orWhere('order_total', '>=', "%{$search_index}%");
                })
                ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                ->orderBy('orders.order_number', $sort_type)->get();
        $tickets = TicketsApiResource::collection($get_tickets);

        $total = Ticket::where(function ($q) use ($search_index) {
                $q->where('ticket_no', 'like', "%{$search_index}%")
                  ->orWhere('title', 'like', "%{$search_index}%")
                  ->orWhere('priority', 'like', "%{$search_index}%")
                  ->orWhere('message', 'like', "%{$search_index}%")
                  ->orWhere('tickets.status', 'like', "%{$search_index}%");
                })
                ->orWhereHas('ticketVendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('ticketOrder', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%");
                                  //->orWhere('order_total', '>=', "%{$search_index}%");
                })
                ->count();

            return response()->json([
                'status_code' => 200,
                'message' => 'success',
                'data' => $tickets,
                'total' => $total,
            ], 200);
          }
          elseif ($ordered_by == 'vendor_name') {
            $get_tickets = Ticket::select('tickets.*')
                             // ->join('orders', 'tickets.order_id', '=', 'orders.id')
                             ->join('add_vendors', 'tickets.vendor_id', '=', 'add_vendors.id')
                             ->where(function ($q) use ($search_index) {
                $q->where('ticket_no', 'like', "%{$search_index}%")
                  ->orWhere('title', 'like', "%{$search_index}%")
                  ->orWhere('priority', 'like', "%{$search_index}%")
                  ->orWhere('message', 'like', "%{$search_index}%")
                  ->orWhere('tickets.status', 'like', "%{$search_index}%");
                })
                ->orWhereHas('ticketVendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('ticketOrder', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%");
                                 // ->orWhere('order_total', '>=', "%{$search_index}%");
                })
                ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                ->orderBy('add_vendors.vendor_name', $sort_type)->get();
        $tickets = TicketsApiResource::collection($get_tickets);

        $total = Ticket::where(function ($q) use ($search_index) {
                $q->where('ticket_no', 'like', "%{$search_index}%")
                  ->orWhere('title', 'like', "%{$search_index}%")
                  ->orWhere('priority', 'like', "%{$search_index}%")
                  ->orWhere('message', 'like', "%{$search_index}%")
                  ->orWhere('tickets.status', 'like', "%{$search_index}%");
                })
                ->orWhereHas('ticketVendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('ticketOrder', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%");
                                 // ->orWhere('order_total', '>=', "%{$search_index}%");
                })
                ->count();

            return response()->json([
                'status_code' => 200,
                'message' => 'success',
                'data' => $tickets,
                'total' => $total,
            ], 200);
          }
          else{
            $get_tickets = Ticket::where(function ($q) use ($search_index) {
                $q->where('ticket_no', 'like', "%{$search_index}%")
                  ->orWhere('title', 'like', "%{$search_index}%")
                  ->orWhere('priority', 'like', "%{$search_index}%")
                  ->orWhere('message', 'like', "%{$search_index}%")
                  ->orWhere('tickets.status', 'like', "%{$search_index}%");
                })
                ->orWhereHas('ticketVendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('ticketOrder', function($q) use ($search_index){
                               $q->where('order_number', 'like', "%{$search_index}%");
                                //->orWhere('order_total', '>=', "%{$search_index}%");
                })
                ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                ->orderBy($ordered_by, $sort_type)->get();
        $tickets = TicketsApiResource::collection($get_tickets);

        $total = Ticket::where(function ($q) use ($search_index) {
                $q->where('ticket_no', 'like', "%{$search_index}%")
                  ->orWhere('title', 'like', "%{$search_index}%")
                  ->orWhere('priority', 'like', "%{$search_index}%")
                  ->orWhere('message', 'like', "%{$search_index}%")
                  ->orWhere('tickets.status', 'like', "%{$search_index}%");
                })
                ->orWhereHas('ticketVendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('ticketOrder', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%");
                                  //->orWhere('order_total', '>=', "%{$search_index}%");
                })
                ->count();

            return response()->json([
                'status_code' => 200,
                'message' => 'success',
                'data' => $tickets,
                'total' => $total,
            ], 200);
          }  
        } 
        // end case admin
        // case vendor search
        elseif (in_array('Vendor', $user_roles)) {
            $vendor       = AddVendor::where('userid_id', Auth::user()->id)->first();
            $vendor_id    = $vendor->id;
           
            if ($ordered_by == 'order_number') {
            $get_tickets = Ticket::select('tickets.*')
                             ->join('orders', 'tickets.order_id', '=', 'orders.id')
                             //->join('add_vendors', 'invoices.vendor_id', '=', 'add_vendors.id')
                             ->where('vendor_id', $vendor_id)
                             ->where(function ($q) use ($search_index) {
                $q->where('ticket_no', 'like', "%{$search_index}%")
                  ->orWhere('title', 'like', "%{$search_index}%")
                  ->orWhere('priority', 'like', "%{$search_index}%")
                  ->orWhere('message', 'like', "%{$search_index}%")
                  ->orWhere('tickets.status', 'like', "%{$search_index}%");
                })
                  ->orWhereHas('ticketVendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('ticketOrder', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%");
                                //  ->orWhere('order_total', '>=', "%{$search_index}%");
                })
            //})
                ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                ->orderBy('orders.order_number', $sort_type)->get();
        $tickets = TicketsApiResource::collection($get_tickets);

        $total = Ticket::where('vendor_id', $vendor_id)
                  ->where(function ($q) use ($search_index) {
                $q->where('ticket_no', 'like', "%{$search_index}%")
                  ->orWhere('title', 'like', "%{$search_index}%")
                  ->orWhere('priority', 'like', "%{$search_index}%")
                  ->orWhere('message', 'like', "%{$search_index}%")
                  ->orWhere('tickets.status', 'like', "%{$search_index}%");
                })
                ->orWhereHas('ticketVendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('ticketOrder', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%");
                                 // ->orWhere('order_total', '>=', "%{$search_index}%");
                })
                ->count();

            return response()->json([
                'status_code' => 200,
                'message' => 'success',
                'data' => $tickets,
                'total' => $total,
            ], 200);
          }
          elseif ($ordered_by == 'vendor_name') {
            $get_tickets = Ticket::select('tickets.*')
                             // ->join('orders', 'tickets.order_id', '=', 'orders.id')
                             ->join('add_vendors', 'tickets.vendor_id', '=', 'add_vendors.id')
                             ->where('vendor_id', $vendor_id)
                             ->where(function ($q) use ($search_index) {
                $q->where('ticket_no', 'like', "%{$search_index}%")
                  ->orWhere('title', 'like', "%{$search_index}%")
                  ->orWhere('priority', 'like', "%{$search_index}%")
                  ->orWhere('message', 'like', "%{$search_index}%")
                  ->orWhere('tickets.status', 'like', "%{$search_index}%");
                })
                ->orWhereHas('ticketVendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('ticketOrder', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%");
                                 // ->orWhere('order_total', '>=', "%{$search_index}%");
                })
                ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                ->orderBy('add_vendors.vendor_name', $sort_type)->get();
        $tickets = TicketsApiResource::collection($get_tickets);

        $total = Ticket::where('vendor_id', $vendor_id)
                  ->where(function ($q) use ($search_index) {
                $q->where('ticket_no', 'like', "%{$search_index}%")
                  ->orWhere('title', 'like', "%{$search_index}%")
                  ->orWhere('priority', 'like', "%{$search_index}%")
                  ->orWhere('message', 'like', "%{$search_index}%")
                  ->orWhere('tickets.status', 'like', "%{$search_index}%");
                })
                ->orWhereHas('ticketVendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('ticketOrder', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%");
                                  //->orWhere('order_total', '>=', "%{$search_index}%");
                })
                ->count();

            return response()->json([
                'status_code' => 200,
                'message' => 'success',
                'data' => $tickets,
                'total' => $total,
            ], 200);
          }
          else{
            $get_tickets = Ticket::where(function ($q) use ($search_index) {
                  $q->where('ticket_no', 'like', "%{$search_index}%")
                  ->orWhere('title', 'like', "%{$search_index}%")
                  ->orWhere('priority', 'like', "%{$search_index}%")
                  ->orWhere('message', 'like', "%{$search_index}%")
                  ->orWhere('tickets.status', 'like', "%{$search_index}%")
                  ->orWhereHas('ticketVendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('ticketOrder', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%");
                                 // ->orWhere('order_total', '>=', "%{$search_index}%");
                });
            })
                ->where('vendor_id', $vendor_id)
                ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                ->orderBy($ordered_by, $sort_type)->get();
        $tickets = TicketsApiResource::collection($get_tickets);

        $total = Ticket::where(function ($q) use ($search_index) {
                  $q->where('ticket_no', 'like', "%{$search_index}%")
                  ->orWhere('title', 'like', "%{$search_index}%")
                  ->orWhere('priority', 'like', "%{$search_index}%")
                  ->orWhere('message', 'like', "%{$search_index}%")
                  ->orWhere('tickets.status', 'like', "%{$search_index}%")
                  ->orWhereHas('ticketVendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('ticketOrder', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%");
                                 // ->orWhere('order_total', '>=', "%{$search_index}%");
                });
            })
                ->where('vendor_id', $vendor_id)
                ->count();

            return response()->json([
                'status_code' => 200,
                'message' => 'success',
                'data' => $tickets,
                'total' => $total,
            ], 200);
          }  
        }// end case vendor
         elseif (in_array('Manager', $user_roles) || in_array('Staff', $user_roles) ) 
      {
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
        $staff_stores = $exist_staff->stores->pluck('id')->toArray();

        if ($ordered_by == 'order_number') {
            $get_tickets = Ticket::select('tickets.*')
                             ->join('orders', 'tickets.order_id', '=', 'orders.id')
                             //->join('add_vendors', 'invoices.vendor_id', '=', 'add_vendors.id')
                             ->where('vendor_id', $vendor_id)
                             ->where(function ($q) use ($search_index) {
                $q->where('ticket_no', 'like', "%{$search_index}%")
                  ->orWhere('title', 'like', "%{$search_index}%")
                  ->orWhere('priority', 'like', "%{$search_index}%")
                  ->orWhere('message', 'like', "%{$search_index}%")
                  ->orWhere('tickets.status', 'like', "%{$search_index}%");
                })
                  ->orWhereHas('ticketVendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('ticketOrder', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%");
                                //  ->orWhere('order_total', '>=', "%{$search_index}%");
                })
            //})
                ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                ->orderBy('orders.order_number', $sort_type)->get();
        $tickets = TicketsApiResource::collection($get_tickets);

        $total = Ticket::where('vendor_id', $vendor_id)
                  ->where(function ($q) use ($search_index) {
                $q->where('ticket_no', 'like', "%{$search_index}%")
                  ->orWhere('title', 'like', "%{$search_index}%")
                  ->orWhere('priority', 'like', "%{$search_index}%")
                  ->orWhere('message', 'like', "%{$search_index}%")
                  ->orWhere('tickets.status', 'like', "%{$search_index}%");
                })
                ->orWhereHas('ticketVendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('ticketOrder', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%");
                                 // ->orWhere('order_total', '>=', "%{$search_index}%");
                })
                ->count();

            return response()->json([
                'status_code' => 200,
                'message' => 'success',
                'data' => $tickets,
                'total' => $total,
            ], 200);
          }
          elseif ($ordered_by == 'vendor_name') {
            $get_tickets = Ticket::select('tickets.*')
                             // ->join('orders', 'tickets.order_id', '=', 'orders.id')
                             ->join('add_vendors', 'tickets.vendor_id', '=', 'add_vendors.id')
                             ->where('vendor_id', $vendor_id)
                             ->where(function ($q) use ($search_index) {
                $q->where('ticket_no', 'like', "%{$search_index}%")
                  ->orWhere('title', 'like', "%{$search_index}%")
                  ->orWhere('priority', 'like', "%{$search_index}%")
                  ->orWhere('message', 'like', "%{$search_index}%")
                  ->orWhere('tickets.status', 'like', "%{$search_index}%");
                })
                ->orWhereHas('ticketVendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('ticketOrder', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%");
                                 // ->orWhere('order_total', '>=', "%{$search_index}%");
                })
                ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                ->orderBy('add_vendors.vendor_name', $sort_type)->get();
        $tickets = TicketsApiResource::collection($get_tickets);

        $total = Ticket::where('vendor_id', $vendor_id)
                  ->where(function ($q) use ($search_index) {
                $q->where('ticket_no', 'like', "%{$search_index}%")
                  ->orWhere('title', 'like', "%{$search_index}%")
                  ->orWhere('priority', 'like', "%{$search_index}%")
                  ->orWhere('message', 'like', "%{$search_index}%")
                  ->orWhere('tickets.status', 'like', "%{$search_index}%");
                })
                ->orWhereHas('ticketVendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('ticketOrder', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%");
                                  //->orWhere('order_total', '>=', "%{$search_index}%");
                })
                ->count();

            return response()->json([
                'status_code' => 200,
                'message' => 'success',
                'data' => $tickets,
                'total' => $total,
            ], 200);
          }
          else{
            $get_tickets = Ticket::where(function ($q) use ($search_index) {
                  $q->where('ticket_no', 'like', "%{$search_index}%")
                  ->orWhere('title', 'like', "%{$search_index}%")
                  ->orWhere('priority', 'like', "%{$search_index}%")
                  ->orWhere('message', 'like', "%{$search_index}%")
                  ->orWhere('tickets.status', 'like', "%{$search_index}%")
                  ->orWhereHas('ticketVendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('ticketOrder', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%");
                                 // ->orWhere('order_total', '>=', "%{$search_index}%");
                });
            })
                ->where('vendor_id', $vendor_id)
                ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                ->orderBy($ordered_by, $sort_type)->get();
        $tickets = TicketsApiResource::collection($get_tickets);

        $total = Ticket::where(function ($q) use ($search_index) {
                  $q->where('ticket_no', 'like', "%{$search_index}%")
                  ->orWhere('title', 'like', "%{$search_index}%")
                  ->orWhere('priority', 'like', "%{$search_index}%")
                  ->orWhere('message', 'like', "%{$search_index}%")
                  ->orWhere('tickets.status', 'like', "%{$search_index}%")
                  ->orWhereHas('ticketVendor', function($q) use ($search_index){
                                $q->where('vendor_name', 'like', "%{$search_index}%");
                })
                ->orWhereHas('ticketOrder', function($q) use ($search_index){
                                $q->where('order_number', 'like', "%{$search_index}%");
                                 // ->orWhere('order_total', '>=', "%{$search_index}%");
                });
            })
                ->where('vendor_id', $vendor_id)
                ->count();

            return response()->json([
                'status_code' => 200,
                'message' => 'success',
                'data' => $tickets,
                'total' => $total,
            ], 200);
          }  
      }
        else{
            return response()->json([
                    'status_code' => 401,
                    'message'  => 'un authorized access page due to permissions',
                   ], 401);
          }
     }
    // end search tickets with name
}
