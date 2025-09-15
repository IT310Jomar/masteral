<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Sales;
use App\Models\Client;
use App\Models\Editor;
use App\Models\Payment;
use App\Models\SchoolType;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\EditorNotification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;
use App\Models\Request as ModelsRequest;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\DitAdsEmail;

class AdminController extends Controller
{


    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['approvedPaid', 'rejectedPaid', 'approvedRequest', 'rejectedRequest', 'requestReUpload', ' view_profile', 'UploadProfile_client', 'UploadProfile_editor', 'editEditorAssigned','getAllPublishedJournal','searchAllPublishedJournal','getRequestData']]);
    }

    // Sales LIST
    public function getSalesData()
    {
        $token = request('token');
        $salesdata = Sales::with(['payment' => function ($query) {
            $query->select('*')->with(['client' => function ($query) {
                $query->select('*');
            }])->with(['modeofpayment' => function ($query) {
                $query->select('*');
            }]);
        }])->latest()->get();

        return response()->json([
            'success' => true,
            'salesdata' => $salesdata,
            'token' => $token

        ], 200);
    } // public function getSalesData()

    public function searchSales()
    {
        $token = request('token');
        $searchQuery = request('query');
        $paymentmode = request('paymentmode');

        if ($searchQuery) {
            $searchSales = Sales::with(['payment', 'payment.client', 'payment.modeofpayment'])
                ->where(function ($query) use ($searchQuery) {
                    $query->orWhereHas('payment.client', function ($query) use ($searchQuery) {
                        $query->whereRaw("CONCAT(firstname, ' ', COALESCE(middlename, ''), ' ', lastname) LIKE ?", ["%{$searchQuery}%"]);
                    })
                        ->orWhereHas('payment.client', function ($query) use ($searchQuery) {
                            $query->whereRaw("CONCAT(firstname, ' ', COALESCE(middlename, ''), ' ', lastname) LIKE ?", ["%{$searchQuery}%"])
                                ->orWhere('reference_number', 'like', "%{$searchQuery}%")
                                ->orWhere('or_number', 'like', "%{$searchQuery}%")
                                ->orWhere('date_of_payment', date('Y-m-d', strtotime($searchQuery)))
                                ->orWhere('payment_status', 'like', "%{$searchQuery}%")
                                ->orWhere('amount', 'LIKE', "%{$searchQuery}%");
                        })->orWhereHas('payment.modeofpayment', function ($query) use ($searchQuery) {
                            $query->where('name', 'like', "%{$searchQuery}%");
                        });
                })->get();

                $totalAmount = 0;
    
                foreach ($searchSales as $sale) {
                    $totalAmount += $sale->payment->amount;
                }

            return response()->json(['success' => true, 'searchSales' => $searchSales, 'totalAmount' => $totalAmount], 200);
        } else {
            $searchSales = Sales::with(['payment', 'payment.client', 'payment.modeofpayment'])
                ->whereHas('payment', function ($query) use ($paymentmode) {
                    $query->where('payment_status', 'Approved')
                        ->where('mode_of_payment_id', $paymentmode);
                })->get();

                $totalAmount = 0;
    
                foreach ($searchSales as $sale) {
                    $totalAmount += $sale->payment->amount;
                }

            return response()->json(['success' => true, 'searchSales' => $searchSales, 'totalAmount' => $totalAmount], 200);
        }
    } // public function searchSales()

    //Sales List Date Range
    public function salesDateRangeSearch()
    {
        $token = request('token');
        $startDate = request('startDate');
        $endDate = request('endDate');
        $paymentmode = request('paymentmode');
    
        if ($paymentmode == 2 || $paymentmode == 1) {
            $salesData = Sales::with(['payment', 'payment.client', 'payment.modeofpayment'])
                ->whereHas('payment', function ($query) use ($paymentmode, $startDate, $endDate) {
                    $query->where('payment_status', 'Approved')
                        ->where('mode_of_payment_id', $paymentmode)
                        ->whereBetween('date_of_payment', [$startDate, $endDate]);
                })->get();
        } else {
            $salesData = Sales::with(['payment', 'payment.client', 'payment.modeofpayment'])
                ->whereHas('payment', function ($query) use ($startDate, $endDate) {
                    $query->where('payment_status', 'Approved')
                        ->whereBetween('date_of_payment', [$startDate, $endDate]);
                })->get();
        }
    
        $totalAmount = 0;
    
        foreach ($salesData as $sale) {
            $totalAmount += $sale->payment->amount;
        }
    
        return response()->json(['success' => true, 'salesData' => $salesData, 'totalAmount' => $totalAmount], 200);
    }
     //public function salesDateRangeSearch()

    // PAID PAYMENT LIST
    public function getPaidAccountData()
    {
        $token = request('token');
        $pendingPayments = Payment::with(['client' => function ($query) {
            $query->select('*');
        }])->with(['modeofpayment' => function ($query) {
            $query->select('*');
        }])->with(['services' => function ($query) {
            $query->select('*');
        }])->where('payment_status', 'Pending')->get();

        $approvedPayments = Payment::with(['client' => function ($query) {
            $query->select('*');
        }])->with(['modeofpayment' => function ($query) {
            $query->select('*');
        }])->with(['services' => function ($query) {
            $query->select('*');
        }])->where('payment_status', 'Approved')->get();

        $rejectedPayments = Payment::with(['client' => function ($query) {
            $query->select('*');
        }])->with(['modeofpayment' => function ($query) {
            $query->select('*');
        }])->with(['services' => function ($query) {
            $query->select('*');
        }])->where('payment_status', 'Rejected')->get();

        return response()->json([
            'success' => true,
            'pendingdata' => $pendingPayments,
            'approveddata' => $approvedPayments,
            'rejecteddata' => $rejectedPayments
        ], 200);
    } // public function getPaidAccountData()

    public function searchPaidAccounts()
    {
        $token = request('token');
        $searchQuery = request('query');
        $paymentmode = request('paymentmode');

        $searchPending = Payment::with(['client', 'modeofpayment'])->where(function ($query) use ($searchQuery) {
            $query->where('reference_number', 'LIKE', ["%{$searchQuery}%"])
                ->orWhere('or_number', 'LIKE', "%{$searchQuery}%")
                ->orWhereDate('date_of_payment', date('Y-m-d', strtotime($searchQuery)))
                ->orWhere('payment_status', 'LIKE', "%{$searchQuery}%")
                ->orWhereHas('modeofpayment', function ($query) use ($searchQuery) {
                    $query->where('name', 'like', "%{$searchQuery}%");
                })
                ->orWhereHas('client', function ($query) use ($searchQuery) {
                    $query->whereRaw("CONCAT(firstname, ' ', COALESCE(middlename, ''), ' ', lastname) LIKE ?", ["%{$searchQuery}%"]);
                });
        })->where('payment_status', 'Pending')->latest()->get();

        if ($searchQuery) {
            $searchApproved = Payment::with(['client', 'modeofpayment'])->where(function ($query) use ($searchQuery) {
                $query->where('reference_number', 'LIKE', ["%{$searchQuery}%"])
                    ->orWhere('or_number', 'LIKE', "%{$searchQuery}%")
                    ->orWhere('date_of_payment', date('Y-m-d', strtotime($searchQuery)))
                    ->orWhere('payment_status', 'LIKE', "%{$searchQuery}%")
                    ->orWhere('amount', 'LIKE', "%{$searchQuery}%")
                    ->orWhereHas('modeofpayment', function ($query) use ($searchQuery) {
                        $query->where('name', 'like', "%{$searchQuery}%");
                    })
                    ->orWhereHas('client', function ($query) use ($searchQuery) {
                        $query->whereRaw("CONCAT(firstname, ' ', COALESCE(middlename, ''), ' ', lastname) LIKE ?", ["%{$searchQuery}%"]);
                    });
            })->where('payment_status', 'Approved')->get();

            return response()->json(['success' => true, 'searchApproved' => $searchApproved], 200);
        } else {
            $searchApproved = Payment::with(['client', 'modeofpayment'])->where('payment_status', 'Approved')->where('mode_of_payment_id', $paymentmode)->get();
            return response()->json(['success' => true, 'searchApproved' => $searchApproved], 200);
        }

        $searchRejected = Payment::with(['client', 'modeofpayment'])->where(function ($query) use ($searchQuery) {
            $query->where('reference_number', 'LIKE', ["%{$searchQuery}%"])
                ->orWhere('or_number', 'LIKE', "%{$searchQuery}%")
                ->orWhere('date_of_payment', date('Y-m-d', strtotime($searchQuery)))
                ->orWhere('payment_status', 'LIKE', "%{$searchQuery}%")
                ->orWhereHas('modeofpayment', function ($query) use ($searchQuery) {
                    $query->where('name', 'like', "%{$searchQuery}%");
                })
                ->orWhereHas('client', function ($query) use ($searchQuery) {
                    $query->whereRaw("CONCAT(firstname, ' ', COALESCE(middlename, ''), ' ', lastname) LIKE ?", ["%{$searchQuery}%"]);
                });
        })->where('payment_status', 'Rejected')->get();

        return response()->json(['token' => $token, 'success' => true, 'searchPending' => $searchPending, 'searchRejected' => $searchRejected], 200);
    } // public function searchPaidAccounts()


    public function approvedPaid($id)
    {

        $status = 'Approved';
        $approvedPaid = Payment::findOrfail($id);
        $approvedPaid->update([
            'payment_status' => $status,
        ]);

        $sales = Sales::create([
            'payment_id' => $approvedPaid->id,
        ]);

        $client_id = Payment::findOrfail($id);
        $notification = Notification::create([
            'payment_id' => $id,
            'client_id' => $client_id->client_id,
            'time' => now(),

        ]);
        return response()->json(['success' => true, 'approvedPaid' => $approvedPaid, 'notification' => $notification, 'sales' => $sales], 200);
    } // public function approvedPaid($id)

    public function rejectedPaid($id)
    {
        $reasons = request('reasons');
        $status = 'Rejected';
        $rejectedPaid = Payment::findOrfail($id);
        $rejectedPaid->update([
            'payment_status' => $status,
            'notes' => $reasons,
        ]);
        $client_id = Payment::findOrfail($id);
        $notification = Notification::create([
            'payment_id' => $id,
            'client_id' => $client_id->client_id,
            'time' => now(),

        ]);
        $data = Client::where('id',  $client_id->client_id)->first();
        $email = User::findOrFail($data->user_id);

        // Message content
        $date =   now();
        $email = $email->email;
        $formattedDate = date("F d, Y", strtotime($date));
        $lastname =   $data->lastname;
        $hello = 'Hello Mr/Ms';

        $subject = 'Payment Rejected ';

        $emailData = [

            'subject' => $subject,
            'message' => $hello . ' ' . $lastname . ' ' . $reasons . ' ' . 'on' . ' ' . $formattedDate,
            // Add any other data you want to pass to the email view here
        ];

        $receiverEmail  = $email; // Replace with the actual receiver's email address

        Mail::to($receiverEmail)->send(new DitAdsEmail($emailData));


        return response()->json(['success' => true, 'rejectedPaid' => $rejectedPaid, 'notification' => $notification], 200);
    } // public function rejectedPaid($id)
    //END

    //REQUEST LIST
    public function getRequestListData()
    {
        $token = request('token');
        $pendingRequest = ModelsRequest::with(['client' => function ($query) {
            $query->select('*');
        }])->with(['payment' => function ($query) {
            $query->select('*');
        }])->with(['editor' => function ($query) {
            $query->select('*');
        }])->where('request_status', 'Pending')->get();

        $approvedRequest = ModelsRequest::with(['client' => function ($query) {
            $query->select('*');
        }])->with(['payment' => function ($query) {
            $query->select('*');
        }])->with(['editor' => function ($query) {
            $query->select('*');
        }])->where('request_status', 'Approved')->get();

        $rejectedRequest = ModelsRequest::with(['client' => function ($query) {
            $query->select('*');
        }])->with(['payment' => function ($query) {
            $query->select('*');
        }])->with(['editor' => function ($query) {
            $query->select('*');
        }])->where('request_status', 'Rejected')->get();

        return response()->json([
            'success' => true,
            'pendingdata' => $pendingRequest,
            'approveddata' => $approvedRequest,
            'rejecteddata' => $rejectedRequest
        ], 200);
    } // public function getRequestListData()

    public function searchRequestList()
    {
        $searchQuery = request('query');
        $token = request('token');
        $searchPending = ModelsRequest::with(['client', 'payment', 'editor'])->where(function ($query) use ($searchQuery) {
            $query->where('date_time_requests', date('Y-m-d', strtotime($searchQuery)))
                ->orWhere('request_status', 'LIKE', "%{$searchQuery}%")
                ->orWhere('uploaded_file', 'LIKE', "%{$searchQuery}%")
                // ->orWhereHas('payment.modeofpayment', function ($query) use ($searchQuery) {
                //     $query->where('name', 'like', "%{$searchQuery}%");
                // })
                ->orWhereHas('client', function ($query) use ($searchQuery) {
                    $query->whereRaw("CONCAT(firstname, ' ', COALESCE(middlename, ''), ' ', lastname) LIKE ?", ["%{$searchQuery}%"]);
                });
        })->where('request_status', 'Pending')->get();

        $searchApproved = ModelsRequest::with(['client', 'payment', 'editor'])->where(function ($query) use ($searchQuery) {
            $query->where('date_time_requests', date('Y-m-d', strtotime($searchQuery)))
                ->orWhere('request_status', 'LIKE', "%{$searchQuery}%")
                ->orWhere('uploaded_file', 'LIKE', "%{$searchQuery}%")
                // ->orWhereHas('payment.modeofpayment', function ($query) use ($searchQuery) {
                //     $query->where('name', 'like', "%{$searchQuery}%");
                // })
                ->orWhereHas('client', function ($query) use ($searchQuery) {
                    $query->whereRaw("CONCAT(firstname, ' ', COALESCE(middlename, ''), ' ', lastname) LIKE ?", ["%{$searchQuery}%"]);
                });
        })->where('request_status', 'Approved')->get();

        $searchRejected = ModelsRequest::with(['client', 'payment', 'editor'])->where(function ($query) use ($searchQuery) {
            $query->where('date_time_requests', date('Y-m-d', strtotime($searchQuery)))
                ->orWhere('request_status', 'LIKE', "%{$searchQuery}%")
                ->orWhere('uploaded_file', 'LIKE', "%{$searchQuery}%")
                // ->orWhereHas('payment.modeofpayment', function ($query) use ($searchQuery) {
                //     $query->where('name', 'like', "%{$searchQuery}%");
                // })
                ->orWhereHas('client', function ($query) use ($searchQuery) {
                    $query->whereRaw("CONCAT(firstname, ' ', COALESCE(middlename, ''), ' ', lastname) LIKE ?", ["%{$searchQuery}%"]);
                });
        })->where('request_status', 'Rejected')->get();

        return response()->json(['success' => true, 'searchPending' => $searchPending, 'searchApproved' => $searchApproved, 'searchRejected' => $searchRejected], 200);
    } // public function searchRequestList()


    public function approvedRequest($id)
    {
        $status = 'Approved';
        $assignEditor = request('editor_id');
        $approvedRequest = ModelsRequest::findOrfail($id);
        $approvedRequest->update([
            'request_status' => $status,
            'editor_id' => $assignEditor,
            'progress_status' => '1',
            'reasons' => null,
        ]);
        $client_id = ModelsRequest::findOrfail($id);
        $notification = Notification::create([
            'payment_id' => $client_id->payment_id,
            'request_id' => $id,
            'client_id' => $client_id->client_id,
            'time' => now(),

        ]);
        $notification = EditorNotification::create([
            'payment_id' => $client_id->payment_id,
            'request_id' => $id,
            'client_id' =>  $client_id->client_id,
            'editor_id' =>  $client_id->editor_id,
            'time' => now(),

        ]);
        return response()->json(['success' => true, 'approvedRequest' => $approvedRequest], 200);
    } // public function approvedRequest($id)

    public function rejectedRequest($id)
    {
        $reasons = request('reasons');
        $status = 'Rejected';
        $rejectedRequest = ModelsRequest::findOrfail($id);
        $rejectedRequest->update([
            'request_status' => $status,
            'notes' => $reasons,
        ]);

        $client_id = ModelsRequest::findOrfail($id);
        $notification = Notification::create([
            'payment_id' => $client_id->payment_id,
            'request_id' => $id,
            'client_id' => $client_id->client_id,
            'time' => now(),

        ]);
        $data = Client::where('id',  $client_id->client_id)->first();
        $email = User::findOrFail($data->user_id);

        // Message content
        $date =   now();
        $email = $email->email;
        $formattedDate = date("F d, Y", strtotime($date));
        $lastname =   $data->lastname;
        $hello = 'Hello Mr/Ms';

        $subject = 'Request Rejected ';

        $emailData = [

            'subject' => $subject,
            'message' => $hello . ' ' . $lastname . ' ' . $reasons . ' ' . 'on' . ' ' . $formattedDate,
            // Add any other data you want to pass to the email view here
        ];

        $receiverEmail  = $email; // Replace with the actual receiver's email address

        Mail::to($receiverEmail)->send(new DitAdsEmail($emailData));
        return response()->json(['success' => true, 'rejectedRequest' => $rejectedRequest], 200);
    } // public function rejectedRequest($id)

    public function getRequestData($id)
    {
        $token = request('token');
        $requestData = ModelsRequest::with(['client' => function ($query) {
            $query->select('*')->with(['users' => function ($query) {
                $query->select('*');
            }]);
        }])->with(['payment' => function ($query) {
            $query->select('*');
        }])->with(['editor' => function ($query) {
            $query->select('*');
        }])->where('id', $id)->get();

        return response()->json(['success' => true, 'requestData' => $requestData], 200);
    } // public function getRequestData($id)

    public function getEditor()
    {
        $token = request('token');
        $editor = Editor::where('status', 'Active')->where('reset_password_status', 0)->get();

        return response()->json(['success' => true, 'editor' => $editor], 200);
    } //public function getEditor(){

    public function requestReUpload(Request $request, $id)
    {
        $pdf = ModelsRequest::findOrFail($id);

        // Validate the input
        $request->validate([
            'filepdf' => 'required|file|mimes:pdf|max:2048', // Corrected validation rule for PDF files
        ]);

        $oldPDF = $pdf->uploaded_file;

        // Store the new PDF if provided
        if ($request->hasFile('filepdf')) {
            $newPDF = $request->file('filepdf');

            // Calculate the hash of the new PDF content
            $newPDFHash = md5(file_get_contents($newPDF->getRealPath()));

            // Calculate the hash of the old PDF content
            if ($oldPDF && Storage::exists('public/journals/' . $oldPDF)) {
                $oldPDFPath = Storage::path('public/journals/' . $oldPDF);
                $oldPDFHash = md5(file_get_contents($oldPDFPath));
            }

            // If the hash of the new PDF is different from the old one, store the new PDF
            if (!isset($oldPDFHash) || $newPDFHash !== $oldPDFHash) {
                if ($oldPDF && Storage::exists('public/journals/' . $oldPDF)) {
                    Storage::delete('public/journals/' . $oldPDF);
                }

                // Use the original name of the uploaded PDF file as the new filename
                $filename = $newPDF->getClientOriginalName();

                // Store the PDF in the 'public' disk (you can configure other disks in config/filesystems.php)
                $newPDF->storeAs('public/journals', $filename);
            } else {
                // If the new PDF is the same as the old one, keep the old PDF
                $filename = $oldPDF;
            }
        } else {
            // If 'filepdf' field is not present in the request, keep the old PDF
            $filename = $oldPDF;
        }

        $pdf->update(['uploaded_file' => $filename]);

        return response()->json(['success' => true, 'message' => 'File uploaded successfully', 'file' => $pdf]);
    } // public function requestReUpload(Request $request)

    //END

    //Paid List Date Range
    public function paidDateRangeSearch()
    {
        $token = request('token');
        $startDate = request('startDate');
        $endDate = request('endDate');
        $paymentmode = request('paymentmode');

        if ($paymentmode == 2) {
            $paidData = Payment::with(['client' => function ($query) {
                $query->select('*');
            }])->with(['modeofpayment' => function ($query) {
                $query->select('*');
            }])->where('payment_status', 'Approved')->where('mode_of_payment_id', $paymentmode)->whereBetween('date_of_payment', [$startDate, $endDate])->get();
            return response()->json(['success' => true, 'paiddata' => $paidData], 200);
        } else if ($paymentmode == 1) {
            $paidData = Payment::with(['client' => function ($query) {
                $query->select('*');
            }])->with(['modeofpayment' => function ($query) {
                $query->select('*');
            }])->where('payment_status', 'Approved')->where('mode_of_payment_id', $paymentmode)->whereBetween('date_of_payment', [$startDate, $endDate])->get();
            return response()->json(['success' => true, 'paiddata' => $paidData], 200);
        } else {
            $paidData = Payment::with(['client' => function ($query) {
                $query->select('*');
            }])->with(['modeofpayment' => function ($query) {
                $query->select('*');
            }])->whereBetween('date_of_payment', [$startDate, $endDate])->where('payment_status', 'Approved')->get();

            return response()->json(['success' => true, 'paiddata' => $paidData,  'token' => $token], 200);
        }
    } //public function paidDateRangeSearch()
    //End

    //View All User Profile
    public function view_profile($id)
    {
        $clientprofile =  Client::with(['users' => function ($q) {
            $q->select('*')->with(['roles' => function ($q) {
                $q->select('*');
            }]);
        }])->with(['schoolType' => function ($q) {
            $q->select('*');
        }])->where('user_id', $id)->get();

        $editorprofile =  Editor::with(['users' => function ($q) {
            $q->select('*')->with(['roles' => function ($q) {
                $q->select('*');
            }]);
        }])->where('user_id', $id)->get();

        return response()->json(['success' => true, 'clientprofile' => $clientprofile, 'editorprofile' => $editorprofile]);
    } //public function view_profile($id) {
    //End

    //EDIT UPLOAD PROFILE IMAGE

    public function UploadProfile_client(Request $request, $id)
    {
        $client_id = request('client_id');
        $email = request('email');

        $profile = Client::findOrFail($id);
        // Validate the input
        $request->validate([
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $oldImage = $profile->image;

        // Store the new image if provided
        if ($request->hasFile('image')) {
            if ($oldImage && Storage::exists('public/profile_image/' . $oldImage)) {
                Storage::delete('public/profile_image/' . $oldImage);
            }
            $newImagePath = $request->file('image')->store('public/profile_image');
            $profile->image = basename($newImagePath);
        }

        $editclient = Client::updateOrCreate(
            ['id' => $id],
            array_filter([
                'firstname' => request('firstname'),
                'lastname' => request('lastname'),
                'middlename' => request('middlename') ?? null,
                'contact' => request('contact_number') ?? null,
                'school' => request('schoolname') ?? null,
                'school_type_id' => request('school_type') ?? null,
                'course' => request('course') ?? null,
            ], function ($value) {
                return !is_null($value);
            })
        );

        // Check if the email already exists in the User model
        $existingUser = User::where('email', $email)->first();

        if ($existingUser && $existingUser->id !== $profile->user_id) {
            return response()->json(["success" => false, "message" => "Email is already in use. Please choose a different email."], 400);
        }

        $edituser = User::find($profile->user_id);

        if ($edituser = User::find($client_id)) {
            if ($edituser->email !== $email) {
                // Update the email only if it's different
                $edituser->update([
                    'email' => $email,
                ]);
            }
        } else {
            $edituser = User::updateOrCreate(
                ['id' => $client_id],
                [
                    'email' => $email,
                    'username' => request('first_name'),
                ]
            );
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = $image->getClientOriginalName();
            $image->storeAs('public/profile_image', $filename); // Store image in storage/public/profile_image
            $profile->image = $filename; // Update image filename in the database
            $profile->save(); // Save the changes
        }

        return response()->json(['success' => true, 'upload' => $profile, 'editclient' => $editclient]);
    } //public function UploadProfile_client(Request $request, $id)

    public function UploadProfile_editor(Request $request, $id)
    {
        $editor_id = request('editor_id');
        $email = request('email');

        $profile = Editor::findOrFail($id);
        // Validate the input
        $request->validate([
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $oldImage = $profile->image;

        // Store the new image if provided
        if ($request->hasFile('image')) {
            if ($oldImage && Storage::exists('public/profile_image/' . $oldImage)) {
                Storage::delete('public/profile_image/' . $oldImage);
            }
            $newImagePath = $request->file('image')->store('public/profile_image');
            $profile->image = basename($newImagePath);
        }

        $editeditor = Editor::updateOrCreate(
            ['id' => $id],
            array_filter([
                'firstname' => request('firstname'),
                'lastname' => request('lastname'),
                'middlename' => request('middlename') ?? null,
                'contact' => request('contact_number') ?? null,
            ], function ($value) {
                return !is_null($value);
            })
        );

        // Check if the email already exists in the User model
        $existingUser = User::where('email', $email)->first();

        if ($existingUser && $existingUser->id !== $profile->user_id) {
            return response()->json(["success" => false, "message" => "Email is already in use. Please choose a different email."], 400);
        }

        $edituser = User::find($profile->user_id);

        if ($edituser = User::find($editor_id)) {
            if ($edituser->email !== $email) {
                // Update the email only if it's different
                $edituser->update([
                    'email' => $email,
                ]);
            }
        } else {
            $edituser = User::updateOrCreate(
                ['id' => $editor_id],
                [
                    'email' => $email,
                    'username' => request('first_name'),
                ]
            );
        }

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = $image->getClientOriginalName();
            $image->storeAs('public/profile_image', $filename); // Store image in storage/public/profile_image
            $profile->image = $filename; // Update image filename in the database
            $profile->save(); // Save the changes
        }

        return response()->json(['success' => true, 'upload' => $profile, 'editeditor' => $editeditor]);
    } //public function UploadProfile_editor(Request $request, $id)

    //END

    public function getSchoolType()
    {
        $token = request('token');
        $types = SchoolType::get();

        return response()->json(['success' => true, 'types' => $types], 200);
    } //public function getSchoolType(){


    public function getSingleEditorPublished($id)
    {
        $token = request('token');
        $publisheddata = ModelsRequest::with(['client' => function ($query) {
            $query->select('*');
        }])->with(['payment' => function ($query) {
            $query->select('*');
        }])->with(['editor' => function ($query) {
            $query->select('*');
        }])->where('request_status', 'Approved')->where('release_status', 0)->where('editor_id', $id)->latest()->get();

        return response()->json([
            'success' => true,
            'PublishedData' => $publisheddata
        ], 200);
    } //public function getSingleEditorPublished($id) {

    public function searchSingleEditorPublishedData($id)
    {
        $token = request('token');
        $searchQuery = request('query');

        $searchPublishedData = ModelsRequest::with(['client', 'payment', 'editor'])->where(function ($query) use ($searchQuery) {
            $query->where('date_of_published', date('Y-m-d', strtotime($searchQuery)))
                ->orWhere('request_status', 'LIKE', "%{$searchQuery}%")
                ->orWhere('editor_uploaded_file', 'LIKE', "%{$searchQuery}%");
            // ->orWhereHas('payment', function ($query) use ($searchQuery) {
            //     $query->where('amount', 'like', "%{$searchQuery}%");
            // });
            // ->orWhereHas('client', function ($query) use ($searchQuery) {
            //     $query->whereRaw("CONCAT(firstname, ' ', COALESCE(middlename, ''), ' ', lastname) LIKE ?", ["%{$searchQuery}%"]);
            // });
        })->where('request_status', 'Approved')->where('release_status', 0)->where('editor_id', $id)->latest()->get();

        return response()->json(['success' => true, 'searchPublishedData' => $searchPublishedData], 200);
    } //public function searchSingleEditorPublishedData($id) {

    public function getAllEditor()
    {
        $token = request('token');
        $editor = Editor::get();

        return response()->json(['success' => true, 'editor' => $editor], 200);
    } //public function getAllEditor(){

    public function editEditorAssigned(Request $request, $id)
    {
        $updateEditor = ModelsRequest::findOrfail($id);

        $updateEditor->update([
            'editor_id' => request('editor')
        ]);
        return response()->json(['success' => true, 'updateEditor' => $updateEditor], 200);
    }

    public function getEditingData()
    {
        $token = request('token');
        $editingdata = ModelsRequest::with(['client' => function ($query) {
            $query->select('*');
        }])->with(['payment' => function ($query) {
            $query->select('*');
        }])->with(['editor' => function ($query) {
            $query->select('*');
        }])->where('editor_status', 'Approved')->where('release_status', 1)->latest()->get();

        return response()->json([
            'success' => true,
            'editingdata' => $editingdata
        ], 200);
    } //public function getEditingData($id) {

    public function searchEditingData()
    {
        $token = request('token');
        $searchQuery = request('query');
        $searchEditingData = ModelsRequest::with(['client', 'payment', 'editor'])->where(function ($query) use ($searchQuery) {
            $query->where('updated_at', date('Y-m-d', strtotime($searchQuery)))
                ->orWhere('editor_status', 'LIKE', "%{$searchQuery}%")
                ->orWhere('uploaded_file', 'LIKE', "%{$searchQuery}%")
                // ->orWhereHas('payment', function ($query) use ($searchQuery) {
                //     $query->where('amount', 'like', "%{$searchQuery}%");
                // });
                ->orWhereHas('client', function ($query) use ($searchQuery) {
                    $query->whereRaw("CONCAT(firstname, ' ', COALESCE(middlename, ''), ' ', lastname) LIKE ?", ["%{$searchQuery}%"]);
                })
                ->orWhereHas('editor', function ($query) use ($searchQuery) {
                    $query->whereRaw("CONCAT(firstname, ' ', COALESCE(middlename, ''), ' ', lastname) LIKE ?", ["%{$searchQuery}%"]);
                });
        })->where('editor_status', 'Approved')->where('release_status', 1)->latest()->get();

        return response()->json(['success' => true, 'searchEditingData' => $searchEditingData], 200);
    } //public function searchEditingData() {

    public function getAllPublishedJournal()
    {
        $token = request('token');
        $Allpublished = ModelsRequest::with(['client' => function ($query) {
            $query->select('*');
        }])->with(['payment' => function ($query) {
            $query->select('*');
        }])->with(['editor' => function ($query) {
            $query->select('*');
        }])->where('editor_status', 'Approved')->where('release_status', 0)->where('progress_status', '3')->latest()->get();

        return response()->json([
            'success' => true,
            'Allpublished' => $Allpublished
        ], 200);
    } //public function getAllPublishedJournal($id) {

    public function searchAllPublishedJournal()
    {
        $searchQuery = request('query');
        $token = request('token');
        $searchAllPublished = ModelsRequest::with(['client', 'payment', 'editor'])->where(function ($query) use ($searchQuery) {
            $query->where('date_of_published', date('Y-m-d', strtotime($searchQuery)))
                ->orWhere('editor_status', 'LIKE', "%{$searchQuery}%")
                ->orWhere('editor_uploaded_file', 'LIKE', "%{$searchQuery}%")
                // ->orWhereHas('payment', function ($query) use ($searchQuery) {
                //     $query->where('amount', 'like', "%{$searchQuery}%");
                // });
                ->orWhereHas('client', function ($query) use ($searchQuery) {
                    $query->whereRaw("CONCAT(firstname, ' ', COALESCE(middlename, ''), ' ', lastname) LIKE ?", ["%{$searchQuery}%"]);
                })
                ->orWhereHas('editor', function ($query) use ($searchQuery) {
                    $query->whereRaw("CONCAT(firstname, ' ', COALESCE(middlename, ''), ' ', lastname) LIKE ?", ["%{$searchQuery}%"]);
                });
        })->where('editor_status', 'Approved')->where('release_status', 0)->where('progress_status', '3')->latest()->get();

        return response()->json(['success' => true, 'searchAllPublished' => $searchAllPublished], 200);
    } //public function searchAllPublishedJournal() {

    //Published List Date Range
    public function publishedDateRangeSearch()
    {
        $token = request('token');
        $startDate = request('startDate');
        $endDate = request('endDate');

        $publishedData = ModelsRequest::with(['client', 'payment', 'editor'])
            ->where('editor_status', 'Approved')
            ->where('request_status', 'Approved')
            ->where('release_status', 0)
            ->whereBetween('date_of_published', [$startDate, $endDate])
            ->get();

        return response()->json(['success' => true, 'publishedData' => $publishedData], 200);
    } //public function publishedDateRangeSearch()
}
