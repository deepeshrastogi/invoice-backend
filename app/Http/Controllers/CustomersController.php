<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customers;
use App\Models\Invoice;
use Illuminate\Pagination\Paginator;
use Auth;
use Illuminate\Validation\Rule;
use Validator;
use DB;

class CustomersController extends Controller
{
    public function __construct()
    {

        $this->middleware('auth:api');
    }

    public function index(Request $request)
    {
        $sortBy = "companyName";
        $sortOrder = "asc";
        $size = ($request->has('size') && !empty($request->size)) ? $request->size : 10; 
        if($request->has('sort_by') && $request->has('order')){
            $sortBy = $request->sort_by;
            $sortOrder = $request->order;
        }

        $customers = Customers::where('user_id',Auth::id())->orderBy($sortBy, $sortOrder)->paginate($size);
        return response()->json([
            'status' => 'success',
            'customers' => $customers,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'companyName' => 'required|string|max:255',
            'phoneNumber' => 'required|string|max:10'
        ]);
        $user_id = Auth::id();

        $customer = Customers::create([
            'user_id'  => $user_id,
            'companyID' => $user_id.rand(1,1000),
            'companyName' => $request->companyName,
            'firstName' => $request->firstName,
            'lastName' => $request->lastName,
            'phoneNumber' => $request->phoneNumber,
            'accountingEmailAddress' => $request->accountingEmailAddress,
            'billingStreetName' => $request->billingStreetName,
            'billingStreetNumber' => $request->billingStreetNumber,
            'billingZipcode' => $request->billingZipcode,
            'billingCity' => $request->billingCity,
            'billingCountry' => $request->billingCountry,
            'billingAdditionalInfo' => $request->billingAdditionalInfo,
            'shippingStreetName' => $request->shippingStreetName,
            'shippingStreetNumber' => $request->shippingStreetNumber,
            'shippingZipcode' => $request->shippingZipcode,
            'shippingCity' => $request->shippingCity,
            'shippingCountry' => $request->shippingCountry,
            'shippingAdditionalInfo' => $request->shippingAdditionalInfo
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Customer created successfully',
            'customer' => $customer,
        ]);
    }

    public function show($id)
    {
        $customer = Customers::where('user_id',Auth::id())->find($id);
        return response()->json([
            'status' => 'success',
            'customer' => $customer,
        ]);
    }

    public function search(Request $request){
        $size = !empty($request->size) ? $request->size : 10;
        $searchText = $request->searchText;
        $customers = Customers::where(function($query) use($searchText){
            $query->where('companyName', 'LIKE', '%' . $searchText . '%')
            ->orWhere('firstName', 'LIKE', '%' . $searchText . '%')
            ->orWhere('lastName', 'LIKE', '%' . $searchText . '%')
            ->orWhere('phoneNumber', 'LIKE', '%' . $searchText . '%')
            ->orWhere('accountingEmailAddress', 'LIKE', '%' . $searchText . '%');
            $searchTextForCustId = str_ireplace(["CUSID","cusid"],["",""],$searchText);
            $query->orWhere('companyID', 'LIKE', '%' . $searchTextForCustId . '%');
        })
        ->where('user_id', Auth::id())
        ->paginate($size);
        return response()->json([
            'status' => 'success',
            'search' => $customers,
        ]);
    }

    public function deleted(Request $request){

        if($request->has('size')){
            $size = $request->size;
        }else{
            $size = 10;
        }

        if($request->has('sort_by') && $request->has('order')){
            $customers = Customers::onlyTrashed()->where('user_id',Auth::id())->orderBy($request->sort_by, $request->order)->paginate($size);
        }else{
            $customers = Customers::onlyTrashed()->where('user_id',Auth::id())->orderBy('companyID','asc')->paginate($size);
        }
        return response()->json([
            'status' => 'success',
            'customers' => $customers,
        ]);
    }
    public function update(Request $request, $id)
    {

        $request->merge(["id"=>$id]);


        $user_id = Auth::id();
        $rules = array(
            'id' => [
                'required',
                Rule::exists('customers', 'id')
                ->where(function ($query) use ($user_id) {
                    $query->where('user_id', $user_id);
                }),
            ], // check id exists with login user
            'companyName' => 'required|string|max:255',
            'phoneNumber' => 'required|string|max:10',
            );

        $validator = Validator::make( $request->all(), $rules);
        if ($validator->fails()) {
            $error = $validator->errors()->first();

            return response()->json([
                'error' => $error,
                'key'=>array_key_first($validator->errors()->messages())
            ], 406);
        }


        $Customers = Customers::where('user_id',Auth::id())->find($id);
        $Customers->companyName = $request->companyName;
        $Customers->firstName = $request->firstName;
        $Customers->lastName = $request->lastName;
        $Customers->phoneNumber = $request->phoneNumber;
        $Customers->accountingEmailAddress = $request->accountingEmailAddress;

        $Customers->billingStreetName = $request->billingStreetName;
        $Customers->billingStreetNumber = $request->billingStreetNumber;
        $Customers->billingZipcode = $request->billingZipcode;
        $Customers->billingCity = $request->billingCity;
        $Customers->billingCountry = $request->billingCountry;
        $Customers->billingAdditionalInfo = $request->billingAdditionalInfo;

        $Customers->shippingStreetName = $request->shippingStreetName;
        $Customers->shippingStreetNumber = $request->shippingStreetNumber;
        $Customers->shippingZipcode = $request->shippingZipcode;
        $Customers->shippingCity = $request->shippingCity;
        $Customers->shippingCountry = $request->shippingCountry;
        $Customers->shippingAdditionalInfo = $request->shippingAdditionalInfo;
        $Customers->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Customer updated successfully',
            'customer' => $Customers,
        ]);
    }

    public function destroy($id)
    {



        $Customers = Customers::where('user_id',Auth::id())->find($id);
        $Customers->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Customer deleted successfully',
        ]);
    }
    public function revoke($id){


        $Customer = Customers::onlyTrashed()->where('user_id',Auth::id())->find($id);
        $Customer->restore();


        return response()->json([
            'status' => 'success',
            'message' => 'Customer restore successfully',
        ]);
    }

    public function filter(Request $request){

        if($request->has('size')){
            $size = $request->size;
        }else{
            $size = 10;
        }

        $customers = Customers::where('user_id',Auth::id())->whereBetween('created_at', [$request->start_date." 00:00:00", $request->end_date." 23:59:59"])->paginate($size);

        return response()->json([
            'status' => 'success',
            'customers' => $customers,
        ]);

    }

    public function frequentCustomers(Request $request){
        $invoiceCustomers = Invoice::select(["user_id"])->groupBy('user_id')->pluck('user_id');
        $customers = Customers::select(['id','companyID','companyName','firstName','lastName','phoneNumber','accountingEmailAddress'])->where('user_id',Auth::id())->whereIn('id',$invoiceCustomers)->orderBy('firstName','asc')->get();
        return response()->json([
            'status' => 'success',
            'customers' => $customers,
        ]);
    }
}
