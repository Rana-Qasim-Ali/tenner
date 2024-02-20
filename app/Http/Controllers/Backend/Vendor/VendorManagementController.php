<?php

namespace App\Http\Controllers\BackEnd\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Vendor;
use Exception;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

class VendorManagementController extends Controller
{
  private $admin_user_name;
  public function __construct()
  {
    $admin = Admin::select('email')->first();
    $this->admin_user_name = $admin->email;
  }

  public function index(Request $request)
  {
    $searchKey = null;

    if ($request->filled('info')) {
      $searchKey = $request['info'];
    }

    return view('backend.end-user.vendor.index');
  }

  public function get_vendor(Request $request)
  {
    // $default_lang = $request->default_lang;
    $vendors = Vendor::orderBy('id', 'desc')
      ->get();

    return Datatables::of($vendors)
      // ->addColumn('checkbox', function ($vendor) {
      //   return '<input type="checkbox" class="bulk-check" data-val="' . $vendor->id . '">';
      // })
      ->addColumn('company_name', function ($vendor) {
        return $vendor->name;
      })
      ->addColumn('email', function ($vendor) {
        return $vendor->email;
      })
      ->addColumn('phone', function ($vendor) {
        return  empty($vendor->phone) ? '-' : $vendor->phone;
      })
      // ->addColumn('account_status', function ($vendor) {
      //   return '<form id="accountStatusForm-' . $vendor->id . '" class="d-inline-block"
      //         action="' . route('admin.organizer_management.organizer.update_account_status', ['id' => $organizer->id]) . '"
      //         method="post">
      //         ' . csrf_field() . '
      //         <select class="form-control form-control-sm ' . ($vendor->status == 1 ? 'bg-success' : 'bg-danger') . '"
      //             name="account_status"
      //             onchange="document.getElementById(\'accountStatusForm-' . $vendor->id . '\').submit()">
      //             <option value="1" ' . ($vendor->status == 1 ? 'selected' : '') . '>
      //                 ' . __('Active') . '
      //             </option>
      //             <option value="0" ' . ($vendor->status == 0 ? 'selected' : '') . '>
      //                 ' . __('Deactive') . '
      //             </option>
      //         </select>
      //     </form>';
      // })

      // ->addColumn('email_status', function ($vendor) {
      //   return '<form id="emailStatusForm-' . $vendor->id . '" class="d-inline-block"
      //       action="' . route('admin.organizer_management.organizer.update_email_status', ['id' => $vendor->id]) . '"
      //       method="post">
      //       ' . csrf_field() . '
      //       <select class="form-control form-control-sm ' . (!is_null($vendor->email_verified_at) ? 'bg-success' : 'bg-danger') . '"
      //           name="email_status"
      //           onchange="document.getElementById(\'emailStatusForm-' . $vendor->id . '\').submit()">
      //           <option value="1" ' . (!is_null($vendor->email_verified_at) ? 'selected' : '') . '>
      //               ' . __('Verified') . '
      //           </option>
      //           <option value="0" ' . (is_null($vendor->email_verified_at) ? 'selected' : '') . '>
      //               ' . __('Not Verified') . '
      //           </option>
      //       </select>
      //   </form>';
      // })

      ->addColumn('actions', function ($vendor) {

        return '  <a href="' . route('admin.edit_management.vendor_edit', ['id' => $vendor->id]) . '"
        class="dropdown-item">
        ' . __('Edit') . '
    </a>

    <form class="deleteForm d-block"
        action="' . route('admin.vendor_management.vendor.delete', ['id' => $vendor->id]) . '"
        method="post">
        ' . csrf_field() . '
        <button type="submit" class="deleteBtn">
            ' . __('Delete') . '
        </button>
    </form>';
        return '<div class="dropdown">
          <button class="btn btn-secondary dropdown-toggle btn-sm" type="button"
              id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              ' . __('Select') . '
          </button>
  
          <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
             
            
          </div>
      </div>';
      })
      ->rawColumns(['checkbox', 'company_name', 'email', 'phone','actions'])
      ->make(true);
  }


  //add
  public function add()
  {
    $languages = Language::get();
    return view('backend.end-user.organizer.create', compact('languages'));
  }
  public function create(Request $request)
  {
    $rules = [
      'company_name' => 'required',
      'phone' => 'required',
      'email' => [
        'required',
        Rule::unique('organizers', 'username')
      ],
      'username' => [
        'required',
        'alpha_dash',
        "not_in:$this->admin_user_name",
        Rule::unique('organizers', 'username')
      ],
    ];

    $languages = Language::get();

    $messages = [];

    foreach ($languages as $language) {
      $rules[$language->code . '_name'] = 'required';
      $messages[$language->code . '_name'] = 'The name field is required for ' . $language->name . ' language.';
    }

    $request->validate($rules, $messages);

    $in = $request->all();
    $in['password'] = Hash::make($request->password);

    $file = $request->file('photo');
    if ($file) {
      $extension = $file->getClientOriginalExtension();
      $directory = public_path('assets/admin/img/organizer-photo/');
      $fileName = uniqid() . '.' . $extension;
      @mkdir($directory, 0775, true);
      $file->move($directory, $fileName);
      $in['photo'] = $fileName;
    }

    $in['status'] = 1;
    $in['email_verified_at'] = now();

    $organizer = Organizer::create($in);

    $languages = Language::get();
    foreach ($languages as $language) {
      $organizer_info = OrganizerInfo::where('organizer_id', $organizer->id)->where('language_id', $language->id)->first();
      if (!$organizer_info) {
        $organizer_info = new OrganizerInfo();
        $organizer_info->language_id = $language->id;
        $organizer_info->organizer_id = $organizer->id;
      }
      $organizer_info->name = $request[$language->code . '_name'];
      $organizer_info->designation = $request[$language->code . '_designation'];
      $organizer_info->country = $request[$language->code . '_country'];
      $organizer_info->city = $request[$language->code . '_city'];
      $organizer_info->state = $request[$language->code . '_state'];
      $organizer_info->zip_code = $request[$language->code . '_zip_code'];
      $organizer_info->address = $request[$language->code . '_address'];
      $organizer_info->details = $request[$language->code . '_details'];
      $organizer_info->save();
    }
    Session::flash('success', 'Added Successfully!');
    return Response::json(['status' => 'success'], 200);
  }

  public function updateEmailStatus(Request $request, $id)
  {
    $organizer = Organizer::find($id);
    if ($request->email_status == 1) {
      $organizer->update(['email_verified_at' => now()]);
    } else {
      $organizer->update(['email_verified_at' => null]);
    }
    Session::flash('success', 'Update Email Verification Status Successfully!');

    return redirect()->back();
  }

  public function show($id)
  {

    $information['langs'] = Language::all();

    $language = Language::where('code', request()->input('language'))->firstOrFail();
    $information['language'] = $language;

    $event_type = request()->input('event_type');


    $events = Event::join('event_contents', 'event_contents.event_id', '=', 'events.id')
      ->join('event_categories', 'event_categories.id', '=', 'event_contents.event_category_id')
      ->where('event_contents.language_id', '=', $language->id)
      ->where('events.organizer_id', '=', $id)
      ->when($event_type, function ($query, $event_type) {
        return $query->where('events.event_type', $event_type);
      })
      ->select('events.*', 'event_contents.id as eventInfoId', 'event_contents.title', 'event_categories.name as category', 'event_contents.slug')
      ->orderByDesc('events.id')
      ->get();

    $information['events'] = $events;

    $organizer = Organizer::findOrFail($id);
    $information['organizer'] = $organizer;

    return view('backend.end-user.organizer.details', $information);
  }
  public function updateAccountStatus(Request $request, $id)
  {

    $user = Organizer::find($id);
    if ($request->account_status == 1) {
      $user->update(['status' => 1]);
    } else {
      $user->update(['status' => 0]);
    }
    Session::flash('success', 'Updated Successfully');

    return redirect()->back();
  }
  public function changePassword($id)
  {
    $userInfo = Organizer::findOrFail($id);

    return view('backend.end-user.organizer.change-password', compact('userInfo'));
  }
  public function updatePassword(Request $request, $id)
  {
    $rules = [
      'new_password' => 'required|confirmed',
      'new_password_confirmation' => 'required'
    ];

    $messages = [
      'new_password.confirmed' => 'Password confirmation does not match.',
      'new_password_confirmation.required' => 'The confirm new password field is required.'
    ];

    $validator = Validator::make($request->all(), $rules, $messages);

    if ($validator->fails()) {
      return Response::json([
        'errors' => $validator->getMessageBag()->toArray()
      ], 400);
    }

    $user = Organizer::find($id);

    $user->update([
      'password' => Hash::make($request->new_password)
    ]);

    Session::flash('success', 'Updated Successfully');

    return Response::json(['status' => 'success'], 200);
  }

  public function edit($id)
  {
    $information = [];
    $languages = Language::get();
    $organizer = Organizer::findOrFail($id);
    $information['organizer'] = $organizer;
    $information['currencyInfo'] = $this->getCurrencyInfo();
    $information['languages'] = $languages;
    return view('backend.end-user.organizer.edit', $information);
  }

  //update
  public function update(Request $request, $id, Organizer $organizer)
  {
    try {
      $rules = [
        'company_name' => 'required',
        'email' => [
          'required',
          Rule::unique('organizers', 'username')->ignore($id)
        ],
        'username' => [
          'required',
          'alpha_dash',
          "not_in:$this->admin_user_name",
          Rule::unique('organizers', 'username')->ignore($id)
        ],
      ];

      $languages = Language::get();

      $messages = [];
      foreach ($languages as $language) {
        $rules[$language->code . '_name'] = 'required';
        $messages[$language->code . '_name'] = 'The name field is required for ' . $language->name . ' language.';
      }

      if ($request->hasFile('photo')) {
        $rules['photo']  = 'dimensions:width=300,height=300';
      }

      $validator = Validator::make($request->all(), $rules, $messages);
      if ($validator->fails()) {
        return Response::json(
          [
            'errors' => $validator->getMessageBag()
          ],
          400
        );
      }


      $in = $request->all();
      $organizer  = Organizer::where('id', $id)->first();
      $file = $request->file('photo');
      if ($file) {
        $extension = $file->getClientOriginalExtension();
        $directory = public_path('assets/admin/img/organizer-photo/');
        $fileName = uniqid() . '.' . $extension;
        @mkdir($directory, 0775, true);
        $file->move($directory, $fileName);

        @unlink(public_path('assets/admin/img/organizer-photo/') . $organizer->photo);
        $in['photo'] = $fileName;
      }
      $organizer->update($in);

      $languages = Language::get();
      foreach ($languages as $language) {
        $organizer_info = OrganizerInfo::where('organizer_id', $organizer->id)->where('language_id', $language->id)->first();
        if (!$organizer_info) {
          $organizer_info = new OrganizerInfo();
          $organizer_info->language_id = $language->id;
          $organizer_info->organizer_id = $organizer->id;
        }
        $organizer_info->name = $request[$language->code . '_name'];
        $organizer_info->designation = $request[$language->code . '_designation'];
        $organizer_info->country = $request[$language->code . '_country'];
        $organizer_info->city = $request[$language->code . '_city'];
        $organizer_info->state = $request[$language->code . '_state'];
        $organizer_info->zip_code = $request[$language->code . '_zip_code'];
        $organizer_info->address = $request[$language->code . '_address'];
        $organizer_info->details = $request[$language->code . '_details'];
        $organizer_info->save();
      }
    } catch (\Exception $th) {
    }
    Session::flash('success', 'Updated Successfully');

    return Response::json(['status' => 'success'], 200);
  }
  //update_organizer_balance
  public function update_organizer_balance(Request $request, $id)
  {
    $organizer  = Organizer::where('id', $id)->first();
    $currency_info = Basic::select('base_currency_symbol_position', 'base_currency_symbol')
      ->first();
    //add or subtract organizer balance
    if ($request->amount_status && $request->amount_status == 1) {
      $amount = $organizer->amount + $request->amount;

      //store data to transcation table
      $transcation = Transaction::create([
        'transcation_id' => time(),
        'booking_id' => NULL,
        'transcation_type' => 4,
        'user_id' => NULL,
        'organizer_id' => $organizer->id,
        'payment_status' => 1,
        'payment_method' => NULL,
        'grand_total' => $request->amount,
        'pre_balance' => $organizer->amount,
        'after_balance' => $amount,
        'gateway_type' => NULL,
        'currency_symbol' => $currency_info->base_currency_symbol,
        'currency_symbol_position' => $currency_info->base_currency_symbol_position,
      ]);

      $organizer_new_amount = $amount;
    } else {
      $amount = $organizer->amount - $request->amount;
      //store data to transcation table
      $transcation = Transaction::create([
        'transcation_id' => time(),
        'booking_id' => NULL,
        'transcation_type' => 5,
        'user_id' => NULL,
        'organizer_id' => $organizer->id,
        'payment_status' => 1,
        'payment_method' => NULL,
        'grand_total' => $request->amount,
        'pre_balance' => $organizer->amount,
        'after_balance' => $amount,
        'gateway_type' => NULL,
        'currency_symbol' => $currency_info->base_currency_symbol,
        'currency_symbol_position' => $currency_info->base_currency_symbol_position,
      ]);

      $organizer_new_amount = $amount;
    }

    //send mail
    if ($request->amount_status == 1 || $request->amount_status == 0) {
      if ($request->amount_status == 1) {
        $template_type = 'balance_add';

        $organizer_alert_msg = "Balance added to organizer account succefully.!";
      } else {
        $template_type = 'balance_subtract';
        $organizer_alert_msg = "Balance Subtract from organizer account succefully.!";
      }
      //mail sending
      // get the website title & mail's smtp information from db
      $info = Basic::select('website_title', 'smtp_status', 'smtp_host', 'smtp_port', 'encryption', 'smtp_username', 'smtp_password', 'from_mail', 'from_name', 'base_currency_symbol_position', 'base_currency_symbol')
        ->first();

      //preparing mail info
      // get the mail template info from db
      $mailTemplate = MailTemplate::query()->where('mail_type', '=', $template_type)->first();
      $mailData['subject'] = $mailTemplate->mail_subject;
      $mailBody = $mailTemplate->mail_body;

      // get the website title info from db
      $website_info = Basic::select('website_title')->first();

      // preparing dynamic data
      $organizerName = $organizer->username;
      $organizerEmail = $organizer->email;
      $organizer_amount = $amount;

      $websiteTitle = $website_info->website_title;

      // replacing with actual data
      $mailBody = str_replace('{transaction_id}', $transcation->transcation_id, $mailBody);
      $mailBody = str_replace('{username}', $organizerName, $mailBody);
      $mailBody = str_replace('{amount}', $info->base_currency_symbol . $request->amount, $mailBody);

      $mailBody = str_replace('{current_balance}', $info->base_currency_symbol . $organizer_amount, $mailBody);
      $mailBody = str_replace('{website_title}', $websiteTitle, $mailBody);

      $mailData['body'] = $mailBody;

      $mailData['recipient'] = $organizerEmail;
      //preparing mail info end

      // initialize a new mail
      $mail = new PHPMailer(true);
      $mail->CharSet = 'UTF-8';
      $mail->Encoding = 'base64';

      // if smtp status == 1, then set some value for PHPMailer
      if ($info->smtp_status == 1) {
        $mail->isSMTP();
        $mail->Host       = $info->smtp_host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $info->smtp_username;
        $mail->Password   = $info->smtp_password;

        if ($info->encryption == 'TLS') {
          $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }

        $mail->Port       = $info->smtp_port;
      }

      // add other informations and send the mail
      try {
        $mail->setFrom($info->from_mail, $info->from_name);
        $mail->addAddress($mailData['recipient']);

        $mail->isHTML(true);
        $mail->Subject = $mailData['subject'];
        $mail->Body = $mailData['body'];

        $mail->send();
        Session::flash('success', $organizer_alert_msg);
      } catch (Exception $e) {
        Session::flash('warning', 'Mail could not be sent. Mailer Error: ' . $mail->ErrorInfo);
      }
      //mail sending end
    }
    $organizer->amount = $organizer_new_amount;
    $organizer->save();
    return Response::json(['status' => 'success'], 200);
  }

  public function destroy($id)
  {
    $organizer = Organizer::find($id);

    $withdraws = $organizer->withdraws()->get();
    foreach ($withdraws as $withdraw) {
      $withdraw->delete();
    }

    $events = Event::where('organizer_id', $organizer->id)->get();
    foreach ($events as $event) {
      @unlink(public_path('assets/admin/img/event/thumbnail/') . $event->thumbnail);
      $event_contents = EventContent::where('event_id', $event->id)->get();
      foreach ($event_contents as $event_content) {
        $event_content->delete();
      }

      $event_images = EventImage::where('event_id', $event->id)->get();
      foreach ($event_images as $event_image) {
        @unlink(public_path('assets/admin/img/event-gallery/') . $event_image->image);
        $event_image->delete();
      }

      //bookings 
      $bookings = $event->booking()->get();
      foreach ($bookings as $booking) {
        // first, delete the attachment
        @unlink(public_path('assets/admin/file/attachments/') . $booking->attachment);

        // second, delete the invoice
        @unlink(public_path('assets/admin/file/invoices/') . $booking->invoice);

        $booking->delete();
      }
      //tickets
      $tickets = $event->tickets()->get();
      foreach ($tickets as $ticket) {
        $ticket->delete();
      }

      // finally delete the event
      $event->delete();
    }

    $organizer->delete();

    return redirect()->back()->with('success', 'Deleted Successfully');
  }

  public function bulkDestroy(Request $request)
  {
    $ids = $request->ids;

    foreach ($ids as $id) {
      $organizer = Organizer::find($id);

      $withdraws = $organizer->withdraws()->get();
      foreach ($withdraws as $withdraw) {
        $withdraw->delete();
      }

      $events = Event::where('organizer_id', $organizer->id)->get();
      foreach ($events as $event) {
        @unlink(public_path('assets/admin/img/event/thumbnail/') . $event->thumbnail);
        $event_contents = EventContent::where('event_id', $event->id)->get();
        foreach ($event_contents as $event_content) {
          $event_content->delete();
        }

        $event_images = EventImage::where('event_id', $event->id)->get();
        foreach ($event_images as $event_image) {
          @unlink(public_path('assets/admin/img/event-gallery/') . $event_image->image);
          $event_image->delete();
        }

        //bookings 
        $bookings = $event->booking()->get();
        foreach ($bookings as $booking) {
          // first, delete the attachment
          @unlink(public_path('assets/admin/file/attachments/') . $booking->attachment);

          // second, delete the invoice
          @unlink(public_path('assets/admin/file/invoices/') . $booking->invoice);

          $booking->delete();
        }
        //tickets
        $tickets = $event->tickets()->get();
        foreach ($tickets as $ticket) {
          $ticket->delete();
        }

        // finally delete the event
        $event->delete();
      }

      $organizer->delete();
    }

    Session::flash('success', 'Deleted Successfully');

    return Response::json(['status' => 'success'], 200);
  }

  //secrtet login
  public function secret_login($id)
  {
    Session::put('secret_login', 1);
    $organizer = Organizer::where('id', $id)->first();
    Auth::guard('organizer')->login($organizer);
    return redirect()->route('organizer.dashboard');
  }

  //update_organizer_balance
  public function send_mail_template()
  {
    //mail sending
    // get the website title & mail's smtp information from db
    $info = Basic::select('website_title', 'smtp_status', 'smtp_host', 'smtp_port', 'encryption', 'smtp_username', 'smtp_password', 'from_mail', 'from_name', 'base_currency_symbol_position', 'base_currency_symbol')
      ->first();

    //preparing mail info


    // get the website title info from db
    $website_info = Basic::select('website_title')->first();


    $websiteTitle = $website_info->website_title;

    // replacing with actual data
    $view = View::make('backend.template-view.index');
    $mailData['subject'] = 'Test Mail Tempate Subject';
    $mailData['body'] = $view;

    $mailData['recipient'] = 'fahadahmadshemul@gmail.com';
    //preparing mail info end

    // initialize a new mail
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';

    // if smtp status == 1, then set some value for PHPMailer
    if ($info->smtp_status == 1) {
      $mail->isSMTP();
      $mail->Host       = $info->smtp_host;
      $mail->SMTPAuth   = true;
      $mail->Username   = $info->smtp_username;
      $mail->Password   = $info->smtp_password;

      if ($info->encryption == 'TLS') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      }

      $mail->Port       = $info->smtp_port;
    }

    // add other informations and send the mail
    try {
      $mail->setFrom($info->from_mail, $info->from_name);
      $mail->addAddress($mailData['recipient']);

      $mail->isHTML(true);
      $mail->Subject = $mailData['subject'];
      $mail->Body = $mailData['body'];

      $mail->send();
      return 'mail send';
    } catch (Exception $e) {
      return $e;
    }
    //mail sending end
  }
}
