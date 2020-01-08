<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use Illuminate\Http\Request;

use App\Users;
use Carbon\Carbon;

class UsersController extends Controller {

    public function __construct()
    {
        //$this->middleware('auth:api');
    }

    //Login and update api key
    public function authenticate(Request $request) {
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required'
        ]);

        $user = Users::where('email', $request->input('email'))->first();

        if($user && Hash::check($request->input('password'), $user->password)) {

            $apikey = base64_encode(Str::Random(40));
            $current = Carbon::now();

            $expire = Carbon::parse($user->expiry);

            if(Str::startsWith($user->api_key, 'expired_') OR $expire->lessThan($current)) {
                $user->api_key = $apikey;
                $user->expiry = $current->addDays(1);
                $user->updated_at = $current;
                $user->save();
            }else {
                $user->updated_at = $current;
                $user->save();
            }


            return response()->json(
                [
                    'status' => 'success',
                    'code' => 200,
                    'data' => $user
                    ]
                );
        }else {
            return response()->json(
                [
                    'status' => 'fail',
                    'code' => 401
                    ]
                );
        }
    }

    //Sign up for an account and create API key
    public function signup(Request $request) {
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required'
        ]);

        $user = Users::where('email', $request->input('email'))->first();

        if($user) {
            return response()->json(
                [
                    'status' => 'fail',
                    'code' => 401,
                    'message' => 'user already exisits'
                    ]
                );

        } else {
            $user = new Users;

            //Save basic user info
            $user->email = $request->email;
            $user->password = Hash::make($request->password , [
                                'memory' => 1024,
                                'time' => 2,
                                'threads' => 2,
                                ]);

            //Create and save API key
            $apikey = base64_encode(Str::Random(40));
            $user->api_key = $apikey;

            //Set Expiry for API key
            $current = Carbon::now();
            $user->expiry = $current->addDays(1);

            $user->save();

            return response()->json(
                [
                    'status' => 'Success',
                    'code' => 200,
                    'data' => $user
                    ]
                );
        }
    }

    //Log out
    public function logout(Request $request) {
        $this->validate($request, [
            'api_key' => 'required',
            'email' => 'required'
        ]);

        $apikey = "expired_" . Str::random(10);
        $current = Carbon::now();

        Users::where('email', $request->input('email'))->update(['api_key' => $apikey, 'expiry' => $current->addDays(1)]);

        return response()->json(
            [
                'status' => 'success',
                'code' => 200,
                'message' => "User has logged out",
                ]
            );

    }

    //Check API KEy
    public function check(Request $request) {

        $check = Users::where('api_key', $request->api_key)->first();

        $current = Carbon::now();
        $tomorrow = Carbon::now()->addDays(1);
        $expire = Carbon::parse($check->expiry);

        if($check && $expire->between($current, $tomorrow)) {
            return response()->json(
                [
                    'status' => 'Success',
                    'code' => 200,
                    'message' => 'Valid user'
                    ]
                );
        } else {
            return response()->json(
                [
                    'status' => 'Failed',
                    'code' => 403,
                    'message' => 'Forbidden'
                    ]
                );
        }
    }
}
