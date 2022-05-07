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
use App\Http\Requests\StoreTicketApiRequest;
use Str;
use Symfony\Component\HttpFoundation\Response;
use Validator;
use App\Http\Controllers\Traits\MediaUploadingTrait;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Api\V1\User\MarkTicketApiRequest;
use App\Mail\SendAdminTicketRequestMail;
use App\Models\User;
use App\Http\Requests\Website\User\Tickets\VendorAnswerTicketApiRequest;
use Illuminate\Support\Facades\Mail;
use App\Models\Vendorstaff;

class TicketApiController extends Controller
{
  use MediaUploadingTrait;

  public function getLang()
  {
      return $lang = \Config::get('app.locale');
  }

    public function index(Request $request)
    {
      $lang = $this->getLang();
      abort_if(Gate::denies('tickets_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
      $request->page == '' ? $page = 1 : $page = $request->page;
      $request->sort_type == '' ? $sort_type = 'DESC' : $sort_type = $request->sort_type;
      $request->ordered_by == '' ? $ordered_by = 'id' : $ordered_by = $request->ordered_by;
      //$PAGINATION_COUNT = \Config::get('constants.pagination.items_per_page');
      $default_count = \Config::get('constants.pagination.items_per_page');
      $request->per_page == '' ? $PAGINATION_COUNT = $default_count : $PAGINATION_COUNT = $request->per_page;
      
      $user = Auth::user();
      $user_roles = $user->roles->pluck('title')->toArray();

      // case logged in user role is Admin (show all invoices)
      if (in_array('Admin', $user_roles)) {
      	if ($ordered_by == 'order_number') {
           $get_tickets = Ticket::select('tickets.*')
                             ->join('orders', 'tickets.order_id', '=', 'orders.id')
                ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                ->orderBy('orders.order_number', $sort_type)->get();
        $tickets = TicketsApiResource::collection($get_tickets);
        $total   = Ticket::count();

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
                           //  ->get();
                             ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                             ->orderBy('add_vendors.vendor_name', $sort_type)->get();
        $tickets = TicketsApiResource::collection($get_tickets);
        $total   = Ticket::count();

            return response()->json([
                'status_code' => 200,
                'message' => 'success',
                'data' => $tickets,
                'total' => $total,
            ], 200);
          }
        else{
            $get_tickets = Ticket::skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                                 ->orderBy($ordered_by, $sort_type)->get();
          //  $get_tickets = Ticket::get();
            $tickets     = TicketsApiResource::collection($get_tickets);
            $total       = Ticket::count();

            return response()->json([
                'status_code' => 200,
                'message' => 'success',
                'data' => $tickets,
                'total' => $total,
            ], 200);
        }  
      } // end admin case
       // case logged in user role is Vendor (show only his invoices)
      elseif (in_array('Vendor', $user_roles)) {
       $vendor    = AddVendor::where('userid_id', Auth::user()->id)->first();
       $vendor_id = $vendor->id;

       if ($ordered_by == 'order_number') {
            $get_tickets = Ticket::select('tickets.*')
                             ->join('orders', 'tickets.order_id', '=', 'orders.id')
                             ->where('vendor_id', $vendor_id)
                             ->orWhere('user_id', Auth::user()->id)//->get();
                             ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                             ->orderBy('orders.order_number', $sort_type)->get();
        $tickets = TicketsApiResource::collection($get_tickets);
        $total   = Ticket::where('vendor_id', $vendor_id)->count();

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
                             ->orWhere('user_id', Auth::user()->id)//->get();
                             ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                             ->orderBy('add_vendors.vendor_name', $sort_type)->get();
        $tickets = TicketsApiResource::collection($get_tickets);
        $total   = Ticket::where('vendor_id', $vendor_id)->count();

            return response()->json([
                'status_code' => 200,
                'message' => 'success',
                'data' => $tickets,
                'total' => $total,
            ], 200);
          }
          else{
            $get_tickets = Ticket::where('vendor_id', $vendor_id)
                                ->orWhere('user_id', Auth::user()->id)//->get();
				               ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
				               ->orderBy($ordered_by, $sort_type)->get();
            $tickets = TicketsApiResource::collection($get_tickets);
            $total   = Ticket::where('vendor_id', $vendor_id)->count();

            return response()->json([
                'status_code' => 200,
                'message' => 'success',
                'data' => $tickets,
                'total' => $total,
            ], 200);
          }  
      } // end case vendor
      /* manager case */
      elseif (in_array('Manager', $user_roles) || in_array('Staff', $user_roles) ) 
      {
       // abort_if(Gate::denies('stores_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
        $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
        $vendor_id     = $vendor->id;
       // $staff_stores = $exist_staff->stores->pluck('id')->toArray();
       // return $staff_stores;

       if ($ordered_by == 'order_number') {
            $get_tickets = Ticket::select('tickets.*')
                             ->join('orders', 'tickets.order_id', '=', 'orders.id')
                             ->where('vendor_id', $vendor_id)
                             ->orWhere('user_id', Auth::user()->id)//->get();
                             ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                             ->orderBy('orders.order_number', $sort_type)->get();
        $tickets = TicketsApiResource::collection($get_tickets);
        $total   = Ticket::where('vendor_id', $vendor_id)->count();

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
                             ->orWhere('user_id', Auth::user()->id)//->get();
                             ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                             ->orderBy('add_vendors.vendor_name', $sort_type)->get();
        $tickets = TicketsApiResource::collection($get_tickets);
        $total   = Ticket::where('vendor_id', $vendor_id)->count();

            return response()->json([
                'status_code' => 200,
                'message' => 'success',
                'data' => $tickets,
                'total' => $total,
            ], 200);
          }
          else{
            $get_tickets = Ticket::where('vendor_id', $vendor_id)
                                ->orWhere('user_id', Auth::user()->id)//->get();
                        ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                        ->orderBy($ordered_by, $sort_type)->get();
            $tickets = TicketsApiResource::collection($get_tickets);
            $total   = Ticket::where('vendor_id', $vendor_id)->count();

            return response()->json([
                'status_code' => 200,
                'message' => 'success',
                'data' => $tickets,
                'total' => $total,
            ], 200);
          }  

      }
      /* manager case */
      elseif (in_array('User', $user_roles)) {
       $user_id = Auth::user()->id;

       if ($ordered_by == 'order_number') {
            $get_tickets = Ticket::select('tickets.*')
                             ->join('orders', 'tickets.order_id', '=', 'orders.id')
                             ->where('user_id', $user_id)//->get();
                             ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                             ->orderBy('orders.order_number', $sort_type)->get();
        $tickets = TicketsApiResource::collection($get_tickets);
        $total   = Ticket::where('user_id', $user_id)->count();

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
                             ->where('user_id', $user_id)
                             //->get();
                             ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                             ->orderBy('add_vendors.vendor_name', $sort_type)->get();
        $tickets = TicketsApiResource::collection($get_tickets);
        $total   = Ticket::where('user_id', $user_id)->count();

            return response()->json([
                'status_code' => 200,
                'message' => 'success',
                'data' => $tickets,
                'total' => $total,
            ], 200);
          }
          else{
            $get_tickets = Ticket::where('user_id', $user_id)//->get();
                       ->skip(($page-1)*$PAGINATION_COUNT)->take($PAGINATION_COUNT)
                        ->orderBy($ordered_by, $sort_type)->get();
            $tickets = TicketsApiResource::collection($get_tickets);
            $total   = Ticket::where('user_id', $user_id)->count();

            return response()->json([
                'status_code' => 200,
                'message' => 'success',
                'data' => $tickets,
                'total' => $total,
            ], 200);
          }  
      } // end case user
      else{
        return response()->json([
                'status_code' => 401,
                'message'  => 'un authorized access page due to permissions',
               ], 401);
      }
    } 
  
    public function show(Ticket $ticket)
    {
        abort_if(Gate::denies('specific_ticket_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();
     // case logged in user role is Admin 
        if (in_array('Admin', $user_roles)) {
            $ticket_data = new ApiSpecificTicketResource($ticket);
            return response()->json([
              'status_code' => 200,
              'message' => 'success',
              'data' => $ticket_data], 200);
        } 
           // case logged in user role is Vendor 
        elseif (in_array('Vendor', $user_roles)) {
                $vendor = AddVendor::where('userid_id', Auth::user()->id)->first();
                if ($ticket->vendor_id == $vendor->id || $ticket->user_id == $user->id) {
                    $ticket_data = new ApiSpecificTicketResource($ticket);
                    return response()->json([
                      'status_code' => 200,
                      'message' => 'success',
                      'data' => $ticket_data], 200);
                }
                else{
                    return response()->json([
                      'status_code' => 401,
                      'message' => 'ticket dos not match'], 401);
                }
        }
        elseif (in_array('User', $user_roles)) {
               // $vendor = AddVendor::where('userid_id', Auth::user()->id)->first();
                if ($ticket->user_id == $user->id) {
                    $ticket_data = new ApiSpecificTicketResource($ticket);
                    return response()->json([
                      'status_code' => 200,
                      'message' => 'success',
                      'data' => $ticket_data], 200);
                }
                else{
                    return response()->json([
                      'status_code' => 401,
                      'message' => 'ticket dos not match'], 401);
                }
        }
         elseif (in_array('Manager', $user_roles) || in_array('Staff', $user_roles) ) 
        {
         // abort_if(Gate::denies('stores_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
          $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
          $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
          $vendor_id     = $vendor->id;

                if ($ticket->vendor_id == $vendor->id || $ticket->user_id == $user->id) {
                    $ticket_data = new ApiSpecificTicketResource($ticket);
                    return response()->json([
                      'status_code' => 200,
                      'message' => 'success',
                      'data' => $ticket_data], 200);
                }
                else{
                    return response()->json([
                      'status_code' => 401,
                      'message' => 'ticket dos not match'], 401);
                }
        }
        else{
            return response()->json([
                    'status_code' => 401,
                    'message'  => 'un authorized access page due to permissions',
                   ], 401);
          }
    }
   
    public function store(StoreTicketApiRequest $request)
    {
        $lang    = $this->getLang();
    		$order   = Order::findOrFail($request->order_id);
        if ($order->paid != 1) {
          return response()->json([
            'status_code' => 400,
            'message' => 'fail',
            'errors' => 'order is still pending'], 400);
        }
        $user       = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();
        if ($user->id != $order->user_id) {
          return response()->json([
            'status_code' => 400,
            'message' => 'fail',
            'errors' => 'order mismatch with user'], 400);
        }
	    	$vendors = $order->orderDetails->pluck('vendor_id')->toArray();
	    	if (!in_array($request->vendor_id, $vendors)) {
	    		return response()->json([
            'status_code' => 400,
            'message' => 'fail',
            'errors' => 'order mismatch with vendor'], 400);
	    	}
      
      $products = $order->orderDetails->where('vendor_id', $request->vendor_id)
                                      ->pluck('product_id')->toArray();

    	if (!in_array($request->product_id, $products)) {
          return response()->json([
            'status_code' => 400,
            'message' => 'fail',
            'errors' => 'order mismatch with product'], 400);
      }

      $exist_ticket  = Ticket::where('user_id', Auth::user()->id)
                          ->where('order_id', $request->order_id)
                          ->where('product_id', $request->product_id)
                          ->first();

      if ($exist_ticket != null) {
        return response()->json([
            'status_code' => 400,
            'message' => 'fail',
            'errors'  => 'You opened ticket before on that product'], 400);
      }

      $request['user_id']       = Auth::user()->id;
      $request['lang']          = $lang;
    	$request['order_id']      = $request->order_id;
    	$request['vendor_id']     = $request->vendor_id;
      $request['product_id']    = $request->product_id;
    	$request['status']        = 'open';
    	$request['ticket_no']     = strtoupper(Str::random(10));

      $ticket      = Ticket::create($request->all());

        /* new */
              if ($request->file('attachment') != '') {
                $commercial_image = $request->file('attachment');
                $path1 = Storage::disk('spaces')
                      ->putFile('tickets/attachments', $commercial_image);
                Storage::disk('spaces')->setVisibility($path1, 'public');
                $url1   = Storage::disk('spaces')->url($path1);
                $ticket->addMediaFromUrl($url1)
                            ->toMediaCollection('attachment');
              }
        $ticket_data = new ApiSpecificTicketResource($ticket);
        return response()->json([
          'status_code' => 201,
         // 'message' => 'success',
          'message' => __('site_messages.New_ticket_opened'),
          'data' => $ticket_data], 201);
    }

    public function solved_ticket(MarkTicketApiRequest $request)
    {
      // abort_if(Gate::denies('specific_ticket_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();
     // case logged in user role is Admin 
        //if (in_array('Admin', $user_roles))
        if (in_array('User', $user_roles) || in_array('Vendor', $user_roles)) 
        {
            $ticket = Ticket::findOrFail($request->id);
             if ($ticket->user_id != $user->id) {
                return response()->json([
                'status_code' => 400,
                'errors' => 'fail, can not edit ticket',
                //'data' => $ticket_data
              ], 400);
            }
            $ticket->update(['case' => 'solved']);
            return response()->json([
                'status_code' => 200,
                'message' => 'success, marked as solved',
                'data'    => $ticket,
              ], 200);
        }
        else{
            return response()->json([
                    'status_code' => 401,
                    'message'  => 'un authorized access page due to permissions',
                   ], 401);
          }

    }

    public function to_admin(MarkTicketApiRequest $request)
    {
        $user = Auth::user();
        $user_roles = $user->roles->pluck('title')->toArray();
     // case logged in user role is Admin 
        if (in_array('User', $user_roles) || in_array('Vendor', $user_roles))
        {
            $ticket = Ticket::findOrFail($request->id);
              if ($ticket->user_id != $user->id) {
                return response()->json([
                'status_code' => 400,
                'errors' => 'fail, can not edit ticket',
                //'data' => $ticket_data
              ], 400);
            }
          
          $admin = User::findOrFail(1);
        Mail::to($admin->email)->send(new SendAdminTicketRequestMail($ticket->ticket_no));

            $ticket->update(['case' => 'to admin']);
            return response()->json([
                'status_code' => 200,
              //  'message' => 'success, delivered to admin',
                'message' => __('site_messages.complaint_escalated_admin'),
                'data'    => $ticket,
              ], 200);
        }
        else{
            return response()->json([
                    'status_code' => 401,
                    'message'  => 'un authorized access page due to permissions',
                   ], 401);
          }
    }

    public function vendor_answer_ticket(VendorAnswerTicketApiRequest $request)
    {
        $lang = $this->getLang();
        $request['lang'] = $lang;
        $user       = Auth::user();
        $user_id    = Auth::user()->id;
        $user_roles = $user->roles->pluck('title')->toArray();

        if (!in_array('Vendor', $user_roles)) {
          return response()->json([
                'status_code' => 401,
                'errors' => 'only ticket vendor related can answer',
                //'data' => $data,
            ], 401);
        }
        $ticket = Ticket::findOrFail($request->ticket_id);
        $vendor   = AddVendor::where('userid_id', $user_id)->first();
        if ($vendor->id != $ticket->vendor_id) {
          return response()->json([
                'status_code' => 401,
                'errors' => 'this ticket does not belong to you to answer',
                //'data' => $data,
            ], 401);
        }
    
      $ticket->update(['answer' => $request->answer]);
      $data = $ticket;
      return response()->json([
                'status_code' => 200,
                'message' => 'success',
                'data' => $data,
            ], 200);
    }

    public function vendor_answer_ticket_edit(VendorAnswerTicketApiRequest $request)
    {
        $lang = $this->getLang();
        $request['lang'] = $lang;
        $user       = Auth::user();
        $user_id    = Auth::user()->id;
        $user_roles = $user->roles->pluck('title')->toArray();

        if (in_array('Vendor', $user_roles)) 
        {  
          /*return response()->json([
                'status_code' => 401,
                'errors' => 'only ticket vendor related can answer',
                //'data' => $data,
            ], 401);  */
       
            $ticket = Ticket::findOrFail($request->ticket_id);
            if ($ticket->status == 'to admin') {
              return response()->json([
                    'status_code' => 400,
                    'errors' => 'can not answer, ticket raised to admin',
                    //'data' => $data,
                ], 400);
            }
            $vendor   = AddVendor::where('userid_id', $user_id)->first();
            $user_id   = User::where('id', $vendor->userid_id)->first()->id;
            if ($vendor->id != $ticket->vendor_id) {
              return response()->json([
                    'status_code' => 401,
                    'errors' => 'this ticket does not belong to you to answer',
                    // 'errors' => __('site_messages.ticket_replied_before'),
                    //'data' => $data,
                ], 401);
            }

            if ($ticket->ticketComments->where('user_id', $user_id)->count() >= 1) {
              return response()->json([
                    'status_code' => 401,
                    'errors' => __('site_messages.ticket_replied_before'),
                    //'data' => $data,
                ], 401);
            }

             $comments = $ticket->ticketComments->pluck('user_id')->toArray();
             $comm_vendors = User::whereIn('id', $comments)->pluck('added_by_id')->toArray();
             if (in_array($user_id, $comm_vendors)) {
              return response()->json([
                    'status_code' => 401,
                    'errors' => 'you just replied on this ticket before',
                    //'data' => $data,
                ], 401);
            } 
        
         // $ticket->update(['answer' => $request->answer]);
            $comment = Ticketcomment::create([
                'ticket_id' => $ticket->id,
                'user_id'   => $user_id,
                'comment'   => $request->answer,
              ]);
               // $ticket->update(['answer' => $request->answer]);
                $data = $comment;
                return response()->json([
                          'status_code' => 200,
                          'message' => 'success',
                          'data' => $data,
                      ], 200); 
      }
       if (in_array('Manager', $user_roles) ||  in_array('Staff', $user_roles) ) 
        {  
            $exist_staff = Vendorstaff::where('email', Auth::user()->email)->first();
            $vendor        = AddVendor::where('id', $exist_staff->vendor_id)->first();
            $vendor_id     = $vendor->id;
            $exist_vendor_id = $vendor->userid_id;
            $staff_stores = $exist_staff->stores->pluck('id')->toArray();
          /*return response()->json([
                'status_code' => 401,
                'errors' => 'only ticket vendor related can answer',
                //'data' => $data,
            ], 401);  */
       
            $ticket = Ticket::findOrFail($request->ticket_id);
            if ($ticket->status == 'to admin') {
              return response()->json([
                    'status_code' => 400,
                    'errors' => 'can not answer, ticket raised to admin',
                    //'data' => $data,
                ], 400);
            }
  
            if ($vendor->id != $ticket->vendor_id) {
              return response()->json([
                    'status_code' => 401,
                    'errors' => 'this ticket does not belong to you to answer',
                    //'data' => $data,
                ], 401);
            }

            if ($ticket->ticketComments->where('user_id', $user_id)->count() >= 1) {
              return response()->json([
                    'status_code' => 401,
                    'errors' => 'you just replied on this ticket before',
                    //'data' => $data,
                ], 401);
            }

            if ($ticket->ticketComments->where('user_id', $exist_vendor_id)->count() >= 1) {
              return response()->json([
                    'status_code' => 401,
                    'errors' => 'you just replied on this ticket before',
                    //'data' => $data,
                ], 401);
            }

            $comments = $ticket->ticketComments->pluck('user_id')->toArray();
            $comm_vendors = User::whereIn('id', $comments)->pluck('added_by_id')->toArray();
             if (in_array($exist_vendor_id, $comm_vendors)) {
              return response()->json([
                    'status_code' => 401,
                    'errors' => 'you just replied on this ticket before',
                    //'data' => $data,
                ], 401);
            } 
        
         // $ticket->update(['answer' => $request->answer]);
            $comment = Ticketcomment::create([
                'ticket_id' => $ticket->id,
                'user_id'   => $user_id,
                'comment'   => $request->answer,
              ]);
               // $ticket->update(['answer' => $request->answer]);
                $data = $comment;
                return response()->json([
                          'status_code' => 200,
                          'message' => 'success',
                          'data' => $data,
                      ], 200); 
      }
    }

    public function admin_answer_ticket(VendorAnswerTicketApiRequest $request)
    {
        $lang = $this->getLang();
        $request['lang'] = $lang;
        $user       = Auth::user();
        $user_id    = Auth::user()->id;
        $user_roles = $user->roles->pluck('title')->toArray();

        if (!in_array('Admin', $user_roles)) {
          return response()->json([
                'status_code' => 401,
                'errors' => 'only admin can answer',
                //'data' => $data,
            ], 401);
        }
        $ticket = Ticket::findOrFail($request->ticket_id);
        // $vendor   = AddVendor::where('userid_id', $user_id)->first();
        if ($ticket->case == 'to admin') 
        {
          $comment = Ticketcomment::create([
            'ticket_id' => $ticket->id,
            'user_id'   => $user_id,
            'comment'   => $request->answer,
          ]);
           // $ticket->update(['answer' => $request->answer]);
            $data = $comment;
            return response()->json([
                      'status_code' => 200,
                      'message' => 'success',
                      'data' => $data,
                  ], 200); 
        }else{
          return response()->json([
                'status_code' => 401,
                'errors' => 'this ticket case have not been raised to admin',
                //'data' => $data,
            ], 401);
        }
    }
}
