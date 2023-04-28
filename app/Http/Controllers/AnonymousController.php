<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Str;
use App\Jobs\ForgetPasswordJob;
use Illuminate\Support\Facades\Validator;

class AnonymousController extends Controller
{
    public function forgot(Request $request)
    {
        $messages = [
			'email.required' => trans('ln_api.email.required'),
			'email.exists' => trans('ln_api.email.exists'),
		];
		
		$validator = Validator::make($request->all(),[
			'email' => 'required|string|email|exists:users,email'
		],$messages);
		
		if ($validator->fails()) {
			return response(['error' => $validator->errors(),'content' => null],401);
		}

        $message = "";
        $row = User::where("email", $request->email)->first();
        $random = Str::random(12);
        $user = User::find($row->id);
        $user->password = Hash::make($random);
        $user->save();
        $message = "Your new password sent to you email";

        $details['email'] = $user->email;
        $details['password'] = $random;
        dispatch(new ForgetPasswordJob($details));

        return response()->json([
            'status' => 'success',
            'message' => $message
        ]);
    }
}
