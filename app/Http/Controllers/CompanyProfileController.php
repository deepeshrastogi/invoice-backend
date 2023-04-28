<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customers;
use App\Models\CompanyProfile;
use Illuminate\Pagination\Paginator;
use Auth;
use Illuminate\Validation\Rule;
use Validator;
use Storage;
class CompanyProfileController extends Controller
{
    
    public function index(Request $request)
    {
        if($request->has('size')){
            $size = $request->size;
        }else{
            $size = 10;
        }

        $is_exists = CompanyProfile::where('user_id',Auth::id())->count();
        if($is_exists>0){
            $company = CompanyProfile::where('user_id',Auth::id())->first();
        }else{
            $company = (object) [];
        }
        return response()->json([
            'status' => 'success',
            'company' => $company,
        ]);
    }

    
    public function saveProfile(Request $request){
        try{

            $validator = Validator::make($request->all(), [
                'company_name'=>'required',
                'authorised_person_name'=>'required',
                'company_number'=>'required',
                'company_phone_number'=>'required',
                'company_address'=>'required',
                'bank_name'=>'required',
                'bank_company_name'=>'required',
                'bank_address'=>'required',
                'bank_account_number'=>'required',
                'bank_ifsc_code'=>'required',
                'bank_swift_code'=>'required',
                'signature_image' => 'required',
                'default_tax'=>'required',
                'invoice_prefix'=>'required',
                'invoice_series'=>'required'
            ]);

            if ($validator->fails()) {

                return response()->json(['error' => $validator->messages()->first()], 406);

            }

            $is_exists = CompanyProfile::where('user_id',Auth::id())->count();
            if($is_exists>0){
                $companies = CompanyProfile::where('user_id',Auth::id())->get();
                foreach($companies as $company){
                    if(!empty($company->signature_image)){
                        Storage::disk('public')->delete($company->signature_image);
                    }
                    $company->delete(); 
                }
            } 
            
            $base64_image = $request->input('signature_image'); // your base64 encoded     
            @list($type, $file_data) = explode(';', $base64_image);
            @list(, $file_data) = explode(',', $file_data); 
            $imageName = Auth::id()."_".time().'.'.'png';  
          
            Storage::disk('local')->put("public/signatures/".$imageName, base64_decode($file_data));
            $location = "signatures/".$imageName;
            $data = $request->all();

            $data['user_id'] = Auth::id();
            $data['signature_image'] = $location;
            $company = CompanyProfile::create($data);

            // All went well
            return response()->json([
                'status' => 'success',
                'company' => $company,
            ]);

        }catch(\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 406);
        }

    }
}
