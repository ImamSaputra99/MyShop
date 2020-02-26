<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Order;
use DB;
use App\Payment;
use Carbon\Carbon;


class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('customer_id',auth()->guard('customer')->user()->id)->orderBy('created_at','DESC')->paginate(10);
        return view('ecommerce.order.index', compact('orders'));
    }

    public function view($invoice)
    {
        $order = Order::with(['district.city.province','details','details.product','payment'])->where('invoice',$invoice)->first();
        return view('ecommerce.order.view',compact('order'));
    }

    public function paymentForm()
    {
        return view('ecommerce.payment');
    }

    public function storePayment(Request $req)
    {
        $this->validate($req,[
            'invoice' => 'required',
            'name' => 'required|string',
            'transfer_to' => 'required|string',
            'transfer_data' => 'required',
            'amount' => 'required|integer',
            'proof' => 'required|image'
        ]);

        DB::beginTransaction();

        try{
            $order = Order::where('invoice', $req->invoice)->first();
            if($order->status == 0 && $req->hasFile('proof')){
                
                $file = $req->file('proof');
                $filename = time() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('public/payment', $filename);

                Payment::create([
                    'order_id' => $req->order_id,
                    'name' => $req->name,
                    'transfer_to' => $req->transfer_to,
                    'transfer_date' => Carbon::parse($req->transfer_date)->format('Y-m-d'),
                    'amount' => $req->mount,
                    'proof' => $req->$filename,
                    'status'=> true,
                ]);

                $order->update(['status' => 1]);

                DB::Commit();

                return redirect()->back()->with(['success' => 'Pesanan Dikonfirmasi']);
            }
            return redirect()->back()->with(['error' => 'Terjadi kesalahan saat konfirmasi']);
        }catch(\Exception $e){
            DB::rollback();
            return redirect()->back()->with(['error' => $e->getMessage]);
        }
    }
}
