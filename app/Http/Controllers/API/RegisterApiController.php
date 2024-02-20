<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User; // Import the Customer model
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Log; // Add the Log facade for logging

class RegisterApiController extends Controller
{
    /**
     * Register a new customer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fname' => 'required',
            'lname' => 'required',
            'email' => 'required|email:rfc,dns|unique:users',
            'password' => [
                'required',
                function ($attribute, $value, $fail) {
                    $errors = [];

                    if (strlen($value) < 8) {
                        $errors[] = 'Requires at least 8 characters.';
                    }
                    // Check for at least one uppercase letter
                    elseif (!preg_match('/[A-Z]/', $value)) {
                        $errors[] = 'A necessary capital letter.';
                    }
                    // Check for at least one special character
                    elseif (!preg_match('/[!@#$%^&*()\-_=+{};:,<.>]/', $value)) {
                        $errors[] = 'At least one symbol required.';
                    }

                    if (!empty($errors)) {
                        $fail(implode(PHP_EOL, $errors));
                    //    / $fail($errors);
                        

                    }
                },
            ],
            'confirm_password' => 'required|same:password',
            'term_condition' => 'accepted',
        ], [
            'term_condition.accepted' => 'You must accept the term conditions.',
            'password.required' => 'The Password Field is Required',
            'password.min' => '8 characters min for the pass',
            'confirm_password.same' => 'Passes do not match',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

            $input = $request->except(['confirm_password']);
            $input['password'] = Hash::make($input['password']);
            $input['term_condition'] = 1;
            
            $user = User::create($input);
 
            if($user){  
                $response = [
                    'data' => 'User Registered Successfully!',
                    'message' => 'success',
                ];
                return response()->json($response, 200);
            }
            else{
                return response()->json(['error' => 'User Not Register'], 400);  
            }
    }
    
    
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            // Attempt to authenticate the customer
            if (Auth::guard('web')->attempt($request->only('email', 'password'))) {
                $customer = Auth::guard('web')->user();
                 
                $token = $customer->createToken('MyApp')->plainTextToken;

                $customer_data = array(
                    'id' => $customer->id,
                    'fname' => $customer->fname,
                    'email' => $customer->email,
                );

                $data = array(
                    'data' => $customer_data,
                    'token' => $token,
                    'message' => 'success'
                );
                return response()->json($data, 200);
             
            } else {
                // Authentication failed
                return response()->json(['error' =>"Incorrect email or password"], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['error' =>"Incorrect email or password"], 400);
        }
    }



    public function social_login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fname' => 'required',
            'provider' => 'required',
            'provider_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            $findcustomer = Customer::where('provider_id', $request->provider_id)->first();
            if ($findcustomer) {
                if ($findcustomer->soft_delete == 0) {
               
                }
                else
                {
                    return response()->json(['error' =>"Email ou mot de passe incorrect"], 400);
                }

                $status = "Email Found";   
                if($findcustomer->email == null){
                    $status = "Email Not found";
                 }
                
                if($status == "Email Found"){ 
                   
                     Auth::guard('customer')->login($findcustomer);
                     $token = $findcustomer->createToken('MyApp')->plainTextToken;  
                     if(isset($request->device_token)){
                        $findcustomer->device_token = $request->device_token ;
                        $findcustomer->save();      
                    }    
                }  
                else{
                    $token = ""; 
                }
                 
                $customer_data = array(
                    'id'    => $findcustomer->id,
                    'fname' => $findcustomer->fname,
                    'email' => $findcustomer->email,
                );

                $data = array(
                    'data' => $customer_data,
                    'token' => $token,
                    'status' => $status,
                    'message' => 'success'
                );
                return response()->json($data, 200);
            } else {
                if (isset($request->email)) {
                    $email = $request->email;
                    $validator = Validator::make($request->all(), [
                        'email' => 'unique:customers,email',
                    ], [
                        'email.unique' => 'This email address is already use to your another account.',
                    ]);
                    
                    if ($validator->fails()) {
                        return response()->json(['errors' => $validator->errors()], 422);
                    }     
                } else {
                    $email = null;
                }
            
          
                 
                $customer = Customer::create([
                    'fname' => $request->fname,
                    'email' => $email,
                    'provider_id' => $request->provider_id,
                    'provider' => $request->provider,
                ]);

                 

                $status = "Email Found";   
                if($customer->email == null){
                    $status = "Email Not found";
                 }
                   
                if($status == "Email Found"){
                    Auth::guard('customer')->login($customer);      
                    $customer->email_verified_at = date('Y-m-d H:i:s');
                    $customer->save();
                    if(isset($request->device_token)){
                        $customer->device_token = $request->device_token ;
                        $customer->save();      
                     }      
                    $token = $customer->createToken('MyApp')->plainTextToken;
                }
                else{
                    $token = "";
                }  
                
                $customer_data = array(
                    'id'    => $customer->id,
                    'fname' => $customer->fname,
                    'email' => $customer->email,
                );

                $data = array(
                    'data' => $customer_data,
                    'token' => $token,
                    'status' => $status,
                    'message' => 'success'
                );
                return response()->json($data, 200);
            }
        } catch (\Exception $e) {
            return response()->json($e->getMessage());
            return response()->json(['error' => 'Clients non autorisés'], 401);
        }
    }

    /**
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        try {
            $customer = $request->user();
            // return response()->json($token);
            // User::find($customer->id)->update(['device_token'=>NULL]);
            $token = $request->user()->currentAccessToken()->delete();

            if ($token) {
                $data = array(
                    'data' => 'Logged out successfully',
                    'message' => 'success'
                );
                return response()->json($data, 200);
            } else {
                return response()->json(['error' => "The User was unable to log out"], 404);
            }

            return response()->json(['error' => "The User was unable to log out"], 500);
        } catch (\Exception $e) {
            // Handle database update error
            return response()->json(['error' => "The User was unable to log out"], 404);
        }
    }
}
