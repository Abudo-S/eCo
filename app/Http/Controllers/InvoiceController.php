<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Auth;
use App\Sold_products;
use App\Invoice;
use App\User;
use App\Traits\Notifications;

class InvoiceController extends Controller
{

    use Notifications;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function __construct()
     {
         $this->middleware('auth');
     }

    public function index()
    {
        //don't forget this data

        // $cart = CartController::checkAdded();
        // $wl = wish_listController::checkAdded();
        // $countNew = NotificationController::checkAdded();
        //
        // $data = [
        //     'notifications' => $notifications,
        //     'cartpros' => $cart,
        //     'wishlistProducts' => $wl,
        //     'countNew' => $countNew
        // ];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        if( isset(Auth::user()->id) ) {

            $cartController = new CartController;
            $products = $cartController->getAllCartProducts();
            $totalCost = $cartController->getTotalCost();

            $total_Cost_For_Each_Product = array();

            foreach($products as $pro) {
                //  $total_Cost_For_Each_Product[$pro->id] = $pro->price * $pro->n_of_pro;
                array_push($total_Cost_For_Each_Product , $pro->price * $pro->n_of_pro);
            }

            $cart = CartController::checkAdded();
            $wl = wish_listController::checkAdded();
            $countNew = NotificationController::checkAdded();

            $data = [
                'cartpros' => $cart,
                'wishlistProducts' => $wl,
                'countNew' => $countNew,
                'products' => $products,
                'subTotalCost' => $totalCost,
                'totalCost_per_prodcut' => $total_Cost_For_Each_Product,
                'eCoPercintage' => "15%",
                'totalCost' => $totalCost + ( $totalCost * 0.15 )
            ];

            return view('invoice.create_invoice')->with($data);
        } else {
            return redirect('/')->with('error', 'You are not authorized to show this page');
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        if( !Auth::guest() ) {
            $this->validate($request, [
                'address' => 'required',
                'country' => 'required',
                'city' => 'required',
                'phone_number' => 'required',
                'zip_code' => 'required',
                'payment_m' => 'required'
            ]);

            // Create Invoice
            $invoice = new Invoice;

            switch ($request->input('payment_m')) {
                case 'Bank Account':
                    $this->validate($request, [
                        'bankNumber' => 'required'
                    ]);
                    $invoice->visaORacc  = $request->input('bankNumber');
                    break;
                case 'Visa':
                    $this->validate($request, [
                        'visaNumber' => 'required'
                    ]);
                    $invoice->visaORacc  = $request->input('visaNumber');
                    break;
                case 'PayPal':
                    $this->validate($request, [
                        'paypalAccount' => 'required'
                    ]);
                    $invoice->visaORacc  = $request->input('paypalAccount');
                    break;

                default:
                    return view("error");
            }

            $invoice->user_id = Auth::user()->id;
            $invoice->address = $request->input('address');
            $invoice->country = $request->input('country');
            $invoice->city = $request->input('city');
            $invoice->phone_number = $request->input('phone_number');
            $invoice->zip_code = $request->input('zip_code');
            $invoice->payment_m = $request->input('payment_m');

            $this->update_recent_data();
            // Store Invoice
            $invoice->save();

            $invoiceID = $invoice->id;

            // Add Sold Products
            $cart = new CartController;

            $products = $cart->getAllCartProducts();

            // Create object from product controller
            $proCtr = new ProductsController;

            foreach($products as $pro)
            {
                $soldProduct = new Sold_products;

                $soldProduct->product_id = $pro->id;
                $soldProduct->invoice_id = $invoiceID;
                $soldProduct->n_of_pro = $pro->n_of_pro;

                $soldProduct->save();

                // Remove a quantity of product from products table
                $proCtr->update_qunatity($pro->id, $pro->n_of_pro);

                // Update number of sold items for each product
                $proCtr->update_nSold($pro->id, $pro->n_of_pro);

            }

            // Removing all products from user's cart
            $cart->remove_all_from_cart();

            $this->userOrder(Auth::user()->id, $invoice->id, "normal");

            return redirect('/products')->with('success', 'Your order is submited');

        } else {
            return redirect('/products')->with('error', 'You are not authorized to add product');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    private function update_recent_data($invoice){
        User::where('id',Auth::user()->id)->update(array('address' => $invoice->address,
         'country' => $invoice->country, 'city' => $invoice->city, 'phone_number' => $invoice->phone_number,
          'zip_code' => $invoice->zip_code, 'payment_m'=> $invoice->payment_m));

    }

    public function use_recent_data(){

    }
}
