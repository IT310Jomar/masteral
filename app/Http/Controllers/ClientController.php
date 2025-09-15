<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use App\Models\AdminNotification;
use Illuminate\Support\Facades\Storage;
use App\Models\Request as ModelsRequest;

class ClientController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['activeClient', 'disableClient', 'myrequestReUpload']]);
    }

    public function getAllClients()
    {
        $token = request('token');
        $client = Client::with(['users' => function ($q) {
            $q->select('*')->with(['roles' => function ($q) {
                $q->select('*');
            }]);
        }])->with(['schoolType' => function ($q) {
            $q->select('*');
        }])->latest()->get();
        return response()->json(["success" => true, 'client' => $client, 'token' => $token], 200);
    } // public function getAllClients(){

    public function searchClient()
    {
        $searchQuery = request('query');
        $token = request('token');
        $searchClient = Client::with(['users' => function ($q) {
            $q->select('*');
        }])->with(['schoolType' => function ($q) {
            $q->select('*');
        }])->whereRaw("CONCAT(firstname, ' ', COALESCE(middlename, ''), ' ', lastname) LIKE ?", ["%{$searchQuery}%"])
            ->orWhere('contact', 'like', "%{$searchQuery}%")
            ->orWhere('course', 'like', "%{$searchQuery}%")
            ->orWhere('school', 'like', "%{$searchQuery}%")
            ->orWhereHas('schoolType', function ($q) use ($searchQuery) {
                $q->select('*')->where('name', 'like', "%{$searchQuery}%");
            })
            ->orWhereHas('users', function ($q) use ($searchQuery) {
                $q->select('*')->where('email', 'like', "%{$searchQuery}%");
            })->latest()->get();

        return response()->json(['success' => true, 'searchClient' => $searchClient], 200);
    } //   public function searchClient()

    public function getSingleClient($id)
    {
        $token = request('token');
        $client = Client::with(['users' => function ($q) {
            $q->select('*')->with(['roles' => function ($q) {
                $q->select('*');
            }]);
        }])->with(['schoolType' => function ($q) {
            $q->select('*');
        }])->where('id',$id)->get();
        return response()->json(["success" => true, 'client' => $client, 'token' => $token], 200);
    }// public function getSingleClient($id)

    public function activeClient($id)
    {
        $status = 'Active';
        $active_client = Client::findOrfail($id);
        $active_client->update([
            'status' => $status,
        ]);
        return response()->json(['success' => true, 'active_client' => $active_client], 200);
    }


    public function disableClient($id)
    {
        $status = 'Disabled';
        $disable_client = Client::findOrfail($id);
        $disable_client->update([
            'status' => $status,
        ]);
        return response()->json(['success' => true, 'disable_client' => $disable_client], 200);
    }

    public function getSingleClientUploadData($id) {
        $token = request('token');
        $uploaddata = ModelsRequest::with(['client' => function($query) {
            $query->select('*');
        }])->with(['payment' => function($query) {
            $query->select('*');
        }])->with(['editor' => function($query) {
            $query->select('*');
        }])->where('client_id', $id)->latest()->get();

        return response()->json([
            'success' => true,
            'uploaddata' => $uploaddata, 'token' => $token
        ], 200);
    }//public function getSingleClientUploadData($id) {

    public function searchSingleClientUploadData($id) {

        $searchQuery = request('query');
        $token = request('token');
        $searchUploadData = ModelsRequest::with(['client','payment','editor'])->where(function ($query) use ($searchQuery) {
            $query->where('date_time_requests', date('Y-m-d', strtotime($searchQuery)))
                ->orWhere('request_status', 'LIKE', "%{$searchQuery}%")
                ->orWhere('uploaded_file', 'LIKE', "%{$searchQuery}%")
                ->orWhereHas('payment', function ($query) use ($searchQuery) {
                    $query->where('amount', 'like', "%{$searchQuery}%");
                });
                // ->orWhereHas('client', function ($query) use ($searchQuery) {
                //     $query->whereRaw("CONCAT(firstname, ' ', COALESCE(middlename, ''), ' ', lastname) LIKE ?", ["%{$searchQuery}%"]);
                // });
        })->where('client_id', $id)->latest()->get();

        return response()->json(['success' => true, 'searchUploadData' => $searchUploadData], 200);
    }//public function searchSingleClientUploadData($id) {

    //PUBLISHED JOURNAL
    public function getClientPublishedJournal($id)
    {
        $token = request('token');
        $Allpublished = ModelsRequest::with(['client' => function ($query) {
            $query->select('*');
        }])->with(['payment' => function ($query) {
            $query->select('*');
        }])->with(['editor' => function ($query) {
            $query->select('*');
        }])->where('client_id', $id)->where('editor_status', 'Approved')->where('release_status', 0)->where('progress_status', '3')->latest()->get();

        return response()->json([
            'success' => true,
            'Allpublished' => $Allpublished, 'token' => $token
        ], 200);
    } //public function getClientPublishedJournal($id) {

    public function searchClientPublishedJournal($id)
    {
        $searchQuery = request('query');
        $token = request('token');
        $formattedSearchQuery = date('Y-m-d', strtotime($searchQuery));

        $searchAllPublished = ModelsRequest::with(['client', 'payment', 'editor'])->where(function ($query) use ($formattedSearchQuery,$searchQuery) {
            $query->where('date_of_published', $formattedSearchQuery)
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
        })->where('client_id', $id)->where('editor_status', 'Approved')->where('release_status', 0)->where('progress_status', '3')->latest()->get();

        return response()->json(['success' => true, 'searchAllPublished' => $searchAllPublished], 200);
    } //public function searchClientPublishedJournal() {

    //Published List Date Range
    public function publishedDateRangeSearchClient($id)
    {
        $startDate = request('startDate');
        $endDate = request('endDate');
        $token = request('token');
        
        $publishedData = ModelsRequest::with(['client', 'payment', 'editor'])
        ->where('editor_status', 'Approved')
        ->where('request_status', 'Approved')
        ->where('release_status', 0)
        ->where('client_id', $id)
        ->whereBetween('date_of_published', [$startDate, $endDate])
        ->get();

        return response()->json(['success' => true, 'publishedData' => $publishedData], 200);
    } //public function publishedDateRangeSearchClient()
    //END

    public function myrequestReUpload(Request $request, $id)
    {
        // $pdf = ModelsRequest::where('request_status', 'Approved')->where('client_id', $id)->get();
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

        $pdf->update([
            'request_status' => "Pending",
            'notes' => null,
            'uploaded_file' => $filename
        ]);

        $notification = AdminNotification::create([
            'payment_id' => $pdf->payment_id,
            'request_id' => $pdf->id,
            'client_id' =>  $pdf->client_id,
            'published' => 0,
            'time' => now(),

        ]);

        return response()->json(['success' => true, 'message' => 'File uploaded successfully', 'file' => $pdf]);
    } // public function myrequestReUpload(Request $request)
}
