<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Models\AdminNotification;
use App\Http\Controllers\Controller;
use App\Models\Request as ModelsRequest;

class RequestController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['requestUpload','journalRequestStatusStatistics']]);
    }

    public function requestUpload(Request $request)
    {
        $client_id = $request->input('client_id');
        $payment_id = $request->input('payment_id');

        $fileModel = new ModelsRequest();

        if ($request->hasFile('filepdf')) {
            $file = $request->file('filepdf');
            $fileName = $file->getClientOriginalName();

            // Save the file to the public directory
            $file->storeAs('public/journals', $fileName);

            $fileModel->uploaded_file = $fileName;
        }

        $fileModel->client_id = $client_id;
        $fileModel->payment_id = $payment_id;
        $fileModel->date_time_requests = now();
        $fileModel->save();

        $paymentUpload = Payment::updateOrCreate(['id' => $payment_id], ['upload_status' => 'Yes']);
        $request_id =  $fileModel->id;
        $notification = AdminNotification::create([
            'payment_id' => $payment_id,
            'client_id' =>$client_id,
            'request_id' => $request_id,
            'time' => now(),

        ]);

        return response()->json(['success' => true, 'message' => 'File uploaded successfully', 'file' => $fileModel, 'paymentUpload' => $paymentUpload]);
    } // public function requestUpload(Request $request)

    public function clientPendingList($id)
    {
        $token = request('token');
        $pending = ModelsRequest::where('request_status', 'Pending')->where('client_id',$id)->with(['editor' => function ($q) {
            $q->select('*');
        }])->latest()->get();
        $approved = ModelsRequest::where('request_status', 'Approved')->where('client_id',$id)->with(['editor' => function ($q) {
            $q->select('*');
        }])->latest()->get();
        $rejected = ModelsRequest::where('request_status', 'Rejected')->where('client_id',$id)->latest()->get();

        return response()->json(["success" => true, 'pending' => $pending, 'approved' =>  $approved, 'rejected' => $rejected, 'token' => $token], 200);
    } // public function clientPendingList(){

    public function searchRequests($id)
    {
        $searchQuery = request('query');
        $token = request('token');
        $pending = ModelsRequest::with(['editor' => function ($q) {
            $q->select('*');
        }])
            ->where('request_status', 'Pending')
            ->where('client_id',$id)
            ->where(function ($query) use ($searchQuery) {
                $query->where('date_time_requests', 'like', "%{$searchQuery}%")
                    ->orWhereDate('date_time_requests', date('Y-m-d', strtotime($searchQuery)));
            })
            ->orWhere('uploaded_file', 'like', "%{$searchQuery}%")
            ->latest()
            ->get();

        $approved = ModelsRequest::with(['editor' => function ($q) {
            $q->select('*');
        }])
            ->where('request_status', 'Approved')
            ->where('client_id',$id)
            ->where(function ($query) use ($searchQuery) {
                $query->where('date_time_requests', 'like', "%{$searchQuery}%")
                    ->orWhereDate('date_time_requests', date('Y-m-d', strtotime($searchQuery)));
            })
            ->orWhere('uploaded_file', 'like', "%{$searchQuery}%")
            ->orWhere(function ($query) use ($searchQuery) {
                $query->orWhereHas('editor', function ($q) use ($searchQuery) {
                    $q->whereRaw("concat(lastname, ' ', firstname, ' ', middlename) like ?", ["%{$searchQuery}%"]);
                });
            })
            ->latest()
            ->get();

        $rejected = ModelsRequest::with(['editor' => function ($q) {
            $q->select('*');
        }])
            ->where('request_status', 'Rejected')
            ->where('client_id',$id)
            ->where(function ($query) use ($searchQuery) {
                $query->where('date_time_requests', 'like', "%{$searchQuery}%")
                    ->orWhereDate('date_time_requests', date('Y-m-d', strtotime($searchQuery)));
            })
            ->orWhere('uploaded_file', 'like', "%{$searchQuery}%")
            ->orWhere('notes', 'like', "%{$searchQuery}%")
            ->latest()
            ->get();


        return response()->json(["success" => true, 'pending' => $pending, 'approved' => $approved, 'rejected' => $rejected], 200);
    } //  public function searchRequests(){
    
    public function journalRequestStatusStatistics(){
        $token = request('token');
        $approved = ModelsRequest::where('request_status','Approved')->count();
    }
}
