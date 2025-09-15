<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Models\ModeOfPayment;
use App\Models\AdminNotification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Models\Request as ModelsRequest;
use App\Models\Services;

class PaymentController extends Controller
{

  public function __construct()
  {
     $this->middleware('auth:api', ['except' => ['paymentData','addpayment', 'edit_payment', 'getDataPayments', 'usersAndPublished', 'journalCount', ' operationalRatio', 'salesStats', 'paymentDataClient', 'paymentDataRequest', 'paymentDataRequestEditor', 'editorPublished', 'schoolStats']]);
  }

    public function getModePayment()
    {
      $token = request('token');
      $mode = ModeOfPayment::where('status', 'Available')->get();

        return response()->json(['success' => true, 'mode' => $mode, 'token' => $token], 200);
    } //public function getModePayment(){

    public function getSingleModePayment($id)
    { 
        $token = request('token');
        $mode = ModeOfPayment::where('id', $id)->where('status', 'Available')->get();

        return response()->json(['success' => true, 'mode' => $mode, 'token' => $token], 200);
    } //public function getSingleModePayment(){

    public function getServices()
    {
        $token = request('token');
        $service = Services::where('status', 'Available')->get();

        return response()->json(['success' => true, 'services' => $service, 'token' => $token], 200);
    } //public function getServices(){

    public function getSingleServices($id)
    {
        $token = request('token');
        $service = Services::where('id', $id)->where('status', 'Available')->get();

        return response()->json(['success' => true, 'services' => $service, 'token' => $token], 200);
    } //public function getSingleServices(){

    public function getPaymentData($id)
    {
        $token = request('token');
        $paymentdata = Payment::where('client_id', $id)->with(['client' => function ($query) {
            $query->select('*');
        }])->with(['modeofpayment' => function ($query) {
            $query->select('*');
        }])->with(['services' => function ($query) {
            $query->select('*');
        }])->latest()->get();

        return response()->json(['success' => true, 'paymentdata' => $paymentdata, 'token' => $token], 200);
    }

    public function searchPayment($id)
    {
        $token = request('token');
        $searchQuery = request('query');

        $searchPayment = Payment::where('client_id', $id)
            ->with(['modeofpayment'])
            ->where(function ($query) use ($searchQuery) {
                $query->where('reference_number', 'LIKE', "%{$searchQuery}%")
                    ->orWhere('or_number', 'LIKE', "%{$searchQuery}%")
                    ->orWhere('amount', 'LIKE', "%{$searchQuery}%")
                    ->orWhereDate('date_of_payment', date('Y-m-d', strtotime($searchQuery)))
                    ->orWhere('payment_status', 'LIKE', "%{$searchQuery}%")
                    ->orWhereHas('modeofpayment', function ($query) use ($searchQuery) {
                        $query->where('name', 'like', "%{$searchQuery}%");
                    });
            })->latest()
            ->get();

        return response()->json(['success' => true, 'searchPayment' => $searchPayment], 200);
    } // public function searchPayment($id)


    public function addpayment(Request $request)
    {
        $client_id = request('client_id');
        $filename = null; // Initialize the filename as null

        if ($request->hasFile('proofofpayment')) {
            $request->validate([
                'proofofpayment' => 'required|max:5000',
            ]);

            $image = $request->file('proofofpayment');

            // Generate a unique filename
            $filename = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

            // Store the image in the 'public' disk (you can configure other disks in config/filesystems.php)
            $image->storeAs('public/proofofpayment_image', $filename);
        }

        $addpaymentData = Payment::create([
            'client_id' => $client_id,
            'service_id' => request('serviceName'),
            'mode_of_payment_id' => request('mode_of_payment_id'),
            'amount' => request('amount'),
            'account' => request('account_sender'),
            'reference_number' => request('reference_number'),
            'or_number' => request('or_number'),
            'date_of_payment' => request('date_of_payment'),
            'proof_of_payment' => $filename,
        ]);
        $paymentId = $addpaymentData->id;
        $notification = AdminNotification::create([
            'payment_id' =>  $paymentId,
            'client_id' => $client_id,
            'time' => now(),

        ]);

        return response()->json(['success' => true, 'addpaymentData' => $addpaymentData], 200);
    } //  public function addpayment(Request $request)

    public function edit_payment(Request $request, $id)
    {
        $client_id = request('client_id');

        $profile = Payment::findOrFail($id);
        // Validate the input
        $request->validate([
            'proofofpayment' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $oldImage = $profile->proof_of_payment;

        // Store the new image if provided
        if ($request->hasFile('proofofpayment')) {
            $newImagePath = $request->file('proofofpayment');

            // Calculate the hash of the new image content
            $newImageHash = md5(file_get_contents($newImagePath->getRealPath()));

            // Calculate the hash of the old image content
            if ($oldImage && Storage::exists('public/proofofpayment_image/' . $oldImage)) {
                $oldImagePath = Storage::path('public/proofofpayment_image/' . $oldImage);
                $oldImageHash = md5(file_get_contents($oldImagePath));
            }

            // If the hash of the new image is different from the old one, store the new image
            if ($newImageHash !== $oldImageHash) {
                if ($oldImage && Storage::exists('public/proofofpayment_image/' . $oldImage)) {
                    Storage::delete('public/proofofpayment_image/' . $oldImage);
                }

                // Generate a unique filename
                $filename = time() . '_' . uniqid() . '.' . $newImagePath->getClientOriginalExtension();

                // Store the image in the 'public' disk (you can configure other disks in config/filesystems.php)
                $newImagePath->storeAs('public/proofofpayment_image', $filename);
            } else {
                // If the new image is the same as the old one, keep the old image
                $filename = $oldImage;
            }
        } else {
            // If 'proofofpayment' field is not present in the request, keep the old image
            $filename = $oldImage;
        }

        $editpayment =  Payment::updateOrCreate(
            ['id' => $id],
            [
                'client_id' => $client_id,
                'service_id' => request('serviceName'),
                'mode_of_payment_id' => request('mode_of_payment_id'),
                'amount' => request('amount'),
                'account' => request('account_sender'),
                'reference_number' => request('reference_number'),
                'or_number' => request('or_number'),
                'date_of_payment' => request('date_of_payment'),
                'proof_of_payment' => $filename,
            ]
        );

        return response()->json(['success' => true, 'editpayment' => $editpayment], 200);
    } //  public function edit_payment(Request $request, $id)

    public function getDataPayments()
    {

        $year = request('year');
        $months = range(1, 12);

        $data = [];
        foreach ($months as $month) {
            $count = Payment::whereYear('date_of_payment', $year)
                ->where('payment_status', 'Approved')
                ->whereMonth('date_of_payment', $month)
                ->count();

            $data[date('F', mktime(0, 0, 0, $month, 1))] = $count;
        }

        $row = [];
        foreach ($months as $month) {
            $counts = ModelsRequest::whereYear('date_time_requests', $year)
                ->where('request_status', 'Approved')
                ->whereMonth('date_time_requests', $month)
                ->count();

            $row[date('F', mktime(0, 0, 0, $month, 1))] = $counts;
        }


        return response()->json(['success' => true, 'data' => $data, 'row' => $row], 200);
    } // public function getDataPayments()

    public function usersAndPublished()
    {
        $year = request('year');
        $months = range(1, 12);
        $data = [];
        foreach ($months as $month) {
            $count = Client::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->count();

            $data[date('F', mktime(0, 0, 0, $month, 1))] = $count;
        }

        $row = [];
        foreach ($months as $month) {
            $counts = ModelsRequest::whereYear('date_of_published', $year)
                ->where('release_status', 0)
                ->whereMonth('date_of_published', $month)
                ->count();

            $row[date('F', mktime(0, 0, 0, $month, 1))] = $counts;
        }


        return response()->json(['success' => true, 'data' => $data, 'row' => $row], 200);
    } // public function usersAndPublished(){

    public function journalCount()
    {
        $pending  =  ModelsRequest::where('request_status', 'Pending')->count();
        $approved = ModelsRequest::where('request_status', 'Approved')->count();
        $rejected = ModelsRequest::where('request_status', 'Rejected')->count();

        return response()->json(['success' => true, 'pending' => $pending, 'approved' => $approved, 'rejected' => $rejected], 200);
    } //   public function journalCount(){

    public function operationalRatio()
    {
        $request = ModelsRequest::count();
        $payment = Payment::count();
        $client  = Client::where('status', 'Active')->count();
        $publish = ModelsRequest::where('release_status', 0)->count();

        return response()->json(['success' => true, 'request' => $request, 'payment' => $payment, 'client' => $client, 'publish' => $publish], 200);
    } //  public function operationalRatio() {

    public function paymentData()
    {
       
        $pending = Payment::where('payment_status', 'Pending')->count();
        $approved = Payment::where('payment_status', 'Approved')->count();
        $rejected = Payment::where('payment_status', 'Rejected')->count();

        return response()->json(['success' => true, 'pending' => $pending, 'approved' => $approved, 'rejected' => $rejected], 200);
    }//public function paymentData()


    public function salesStats()
    {
        $year = request('year');
        $months = range(1, 12);
        $sales = [];

        foreach ($months as $month) {
            $sum = Payment::select('amount')
                ->whereYear('date_of_payment', $year)
                ->whereMonth('date_of_payment', $month)
                ->where('payment_status', 'Approved')
                ->sum('amount');

            $sales[date('F', mktime(0, 0, 0, $month, 1))] = $sum;
        }

        // Calculate the total sales for the year
        $totalSales = array_sum($sales);

        // Add the total sales with alias "Total" to the $sales array
        $sales['Total'] = $totalSales;

        return response()->json(['success' => true, 'sales' => $sales], 200);
    } //public function salesStats()

    public function paymentDataClient($id)
    {
        $pending = Payment::where('payment_status', 'Pending')->where('client_id',$id)->count();
        $approved = Payment::where('payment_status', 'Approved')->where('client_id',$id)->count();
        $rejected = Payment::where('payment_status', 'Rejected')->where('client_id',$id)->count();

        return response()->json(['success' => true, 'pending' => $pending, 'approved' => $approved, 'rejected' => $rejected], 200);
    }//public function paymentDataClient($id)
    public function paymentDataRequest($id)
    {
        $pending = ModelsRequest::where('request_status', 'Pending')->where('client_id',$id)->count();
        $approved =  ModelsRequest::where('request_status', 'Approved')->where('client_id',$id)->count();
        $rejected =  ModelsRequest::where('request_status', 'Rejected')->where('client_id',$id)->count();

        return response()->json(['success' => true, 'pending' => $pending, 'approved' => $approved, 'rejected' => $rejected], 200);
    }//public function paymentDataRequest($id)
    public function paymentDataRequestEditor($id)
    {
        $pending = ModelsRequest::where('editor_status', 'Pending')->where('editor_id',$id)->count();
        $approved =  ModelsRequest::where('editor_status', 'Approved')->where('editor_id',$id)->count();
        $total =  ModelsRequest::where('editor_id',$id)->count();

        return response()->json(['success' => true, 'pending' => $pending, 'approved' => $approved, 'total' => $total], 200);
    } //public function paymentDataRequestEditor($id)

    public function editorPublished($id)
    {
        $year = request('year');
        $months = range(1, 12);
        $published = [];
        foreach ($months as $month) {
            $count = ModelsRequest::whereYear('date_of_published', $year)
                ->where('editor_id', $id)
                ->whereMonth('date_of_published', $month)
                ->count();

            $published[date('F', mktime(0, 0, 0, $month, 1))] = $count;
        }

     

        return response()->json(['success' => true, 'published' => $published, ], 200);
    }// public function editorPublished($id)

    
    public function schoolStats()
    {
        $year = request('year');
        $months = range(1, 12);
        $public = [];
        foreach ($months as $month) {
            $count = ModelsRequest::with(['client' => function($q){
                $q->select('*')->where('school_type_id',1);
            }]) ->whereHas('client', function ($q) {
                $q->where('school_type_id', 1);
            })->whereYear('date_of_published', $year)
                ->whereMonth('date_of_published', $month)
                ->count();

            $public[date('F', mktime(0, 0, 0, $month, 1))] = $count;
        }
        $private = [];
        foreach ($months as $month) {
            $count = ModelsRequest::with(['client' => function($q){
                $q->select('*')->where('school_type_id',2);
            }]) ->whereHas('client', function ($q) {
                $q->where('school_type_id', 2);
            })->whereYear('date_of_published', $year)
                ->whereMonth('date_of_published', $month)
                ->count();

            $private[date('F', mktime(0, 0, 0, $month, 1))] = $count;
        }

     

        return response()->json(['success' => true, 'public' => $public, 'private' => $private], 200);
    }//  public function schoolStats()

}
