<?php

namespace App\Http\Controllers\BackEnd;

use App\Http\Controllers\Controller;
// use App\Http\Helpers\UploadFile;
use App\Models\Admin;
// use App\Rules\ImageMimeTypeRule;
// use App\Rules\MatchEmailRule;
// use App\Rules\MatchOldPasswordRule;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
// use Illuminate\Validation\Rule;
// use PHPMailer\PHPMailer\Exception;
// use PHPMailer\PHPMailer\PHPMailer;

use Yajra\DataTables\Facades\DataTables;

class AdminController extends Controller
{
  public function login()
  {
    return view('backend.sign-in');
  }

  public function signup()
  {
    return view('backend.sign-up');
  }


  public function create(Request $request)
  {
    $rules = [
      'email' => 'required|email|unique:vendors',
      'password' => 'required|confirmed|min:6',
    ];
    $messages = [];
    $validator = Validator::make($request->all(), $rules, $messages);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator->errors());
    }

    $in = $request->all();
    $in['password'] = Hash::make($request->password);
    $organizer = Admin::create($in);
    return redirect()->route('admin.login');
  }



  public function authentication(Request $request)
  {
    $rules = [
      'email' => 'required',
      'password' => 'required'
    ];

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
      return redirect()->back()->withErrors($validator->errors());
    }

    if (
      Auth::guard('admin')->attempt([
        'email' => $request->email,
        'password' => $request->password
      ])
    ) {
      $authAdmin = Auth::guard('admin')->user();
      return redirect()->route('admin.dashboard');
      // check whether the admin's account is active or not
      if ($authAdmin->status == 0) {
        Session::flash('alert', 'Sorry, your account has been deactivated!');

        // logout auth admin as condition not satisfied
        Auth::guard('admin')->logout();

        return redirect()->back();
      } else {
        return redirect()->route('admin.dashboard');
      }
    } else {
      return redirect()->back()->with('alert', 'Oops, Email or password does not match!');
    }
  }

  public function forgetPassword()
  {
    return view('backend.forget-password');
  }
  
  public function redirectToDashboard()
  {
    return view('backend.admin.dashboard');
  }

  public function editProfile()
  {
    $adminInfo = Auth::guard('admin')->user();

    return view('backend.admin.edit-profile', compact('adminInfo'));
  }


  public function changePassword()
  {
    return view('backend.admin.change-password');
  }

  
  public function logout(Request $request)
  {
    Auth::guard('admin')->logout();

    return redirect()->route('admin.login');
  }
}
