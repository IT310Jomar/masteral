<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Editor;
use Illuminate\Support\Str;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\AdminNotification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\Request as ModelsRequest;

class EditorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['postEditor','editEditor','activeEditor','disableEditor','getEditorResetPass','approvedAssignedDocs','rejectedAssignedDocs','requestEditedUpload','publishedJournal']]);
      
    }
    public function getAllDataEditor()
    {
        $token = request('token');
        $editor = Editor::with(['users' => function($q) {
            $q->select('*')->with(['roles' => function($q) {
                $q->select('*');
            }]);
        }])->latest()->get();
        return response()->json(["success" => true, 'editor' => $editor], 200);
    } //public function getAllDataEditor(){

    public function getEditorResetPass($id)
    {
        $editor = Editor::where('id',$id)->with(['users' => function($q) {
            $q->select('*')->with(['roles' => function($q) {
                $q->select('*');
            }]);
        }])->where('reset_password_status', 1)->where('status', 'Active')->get();
        return response()->json(["success" => true, 'editor' => $editor], 200);
    } //public function getEditorResetPass(){

    public function postEditor()
    {
        $email = request('email');

        // Check if the email already exists in the User model
        $existingUser = User::where('email', $email)->first();
    
        if ($existingUser) {
            return response()->json(["success" => false, "message" => "Email is already in use. Please choose a different email."], 400);
        }

        $adduser = User::create([
            'username' => request('firstname'),
            'email' => $email,
            'password' => bcrypt('password'),
            'role_id' => 3,
            'verification_token' => Str::random(60), // Generate a unique token for email verification
            'verified' => false, // Mark the user as unverified
        ]);

        $verificationToken = $adduser->verification_token;

        Mail::send('email.verification', ['token' => $verificationToken, 'user' => $adduser], function ($message) use ($adduser) {
            $message->to($adduser->email);
            $message->subject('Email Verification');
        });
        $postEditor = Editor::create([
            'user_id' => $adduser->id,
            'firstname' => request('firstname'),
            'middlename' => request('middlename'),
            'lastname' => request('lastname'),
            'contact' => request('contact_number'),
        ]);

        return response()->json(["success" => true, 'adduser' => $adduser, 'postEditor' => $postEditor], 200);
    } //public function postEditor() {

    public function editEditor($id)
    {
        $email = request('email');
        $editor = Editor::find($id);

        // Check if the email already exists in the User model
        $existingUser = User::where('email', $email)->first();

        if ($existingUser && $existingUser->id !== $editor->user_id) {
            return response()->json(["success" => false, "message" => "Email is already in use. Please choose a different email."], 400);
        }

        $editEditor = Editor::updateOrCreate(
            ['id' => $id],
            [
                'firstname' => request('firstname'),
                'middlename' => request('middlename'),
                'lastname' => request('lastname'),
                'contact' => request('contact_number'),
            ]
        );

        $edituser = User::find($editor->user_id);

        if ($edituser) {
            if ($edituser->email !== $email) {
                // Update the email only if it's different
                $edituser->update([
                    'email' => $email,
                    'username' => request('firstname'),
                ]);
            }
        } else {
            $edituser = User::updateOrCreate(
                ['id' => $editor->user_id],
                [
                    'email' => $email,
                    'username' => request('firstname'),
                ]
            );
        }

        return response()->json(["success" => true, 'editEditor' => $editEditor, 'edituser' => $edituser], 200);
    }


    public function searchEditor()
    {
        $searchQuery = request('query');
        $token = request('token');
        $searchEditor = Editor::where(function ($query) use ($searchQuery) {
            $query->whereRaw("CONCAT(firstname, ' ', COALESCE(middlename, ''), ' ', lastname) LIKE ?", ["%{$searchQuery}%"])
                ->orWhere('contact', 'LIKE', "%{$searchQuery}%")
                ->orWhere('status', 'LIKE', "%{$searchQuery}%");
        })->latest()->get();

        return response()->json(['success' => true, 'searchEditor' => $searchEditor], 200);
    } // public function searchEditor()
    public function getSingleEditor($id)
    {
        $token = request('token');
        $editor = Editor::with(['users' => function ($q) {
            $q->select('*')->with(['roles' => function ($q) {
                $q->select('*');
            }]);
        }])->findOrFail($id);
        return response()->json(["success" => true, 'editor' => $editor], 200);
    }//public function getSingleEditor($id)


    public function activeEditor($id)
    {
        $status = 'Active';
        $active_editor = Editor::findOrfail($id);
        $active_editor->update([
            'status' => $status,
        ]);
        return response()->json(['success' => true, 'active_editor' => $active_editor], 200);
    }//public function activeEditor($id)


    public function disableEditor($id)
    {
        $status = 'Disabled';
        $disable_editor = Editor::findOrfail($id);
        $disable_editor->update([
            'status' => $status,
        ]);
        return response()->json(['success' => true, 'disable_editor' => $disable_editor], 200);
    }//public function disableEditor($id)

    public function getAssignedData($id)
    {
        $token = request('token');
        $approvedRequest = ModelsRequest::where('editor_id', $id)->where('progress_status', '1')->where('request_status', 'Approved')->where('edited_upload', 1)->with(['client' => function($query) {
            $query->select('*');
        }])->with(['payment' => function($query) {
            $query->select('*');
        }])->with(['editor' => function($query) {
            $query->select('*');
        }])->latest()->get();

        return response()->json([
            'success' => true,
            'approveddata' => $approvedRequest,
        ], 200);
    }// public function getAssignedData()

    public function searchAssignedList($id)
    {
        $searchQuery = request('query');
        $token = request('token');
        $formattedSearchQuery = date('Y-m-d', strtotime($searchQuery)); // Convert the search query to 'Y-m-d' format
    
        $searchAssigned = ModelsRequest::where('editor_id', $id)->where('progress_status', '1')
            ->with(['client', 'payment', 'editor'])
            ->where(function ($query) use ($formattedSearchQuery, $searchQuery) {
                $query->where('uploaded_file', 'LIKE', "%{$searchQuery}%")
                    ->orWhere('request_status', 'LIKE', "%{$searchQuery}%")
                    ->orWhereDate('date_time_requests', $formattedSearchQuery)
                    ->orWhereHas('client', function ($query) use ($searchQuery) {
                        $query->whereRaw("CONCAT(firstname, ' ', COALESCE(middlename, ''), ' ', lastname) LIKE ?", ["%{$searchQuery}%"]);
                    });
            })
            ->where('request_status', 'Approved')->where('edited_upload', 1)
            ->get();
    
        return response()->json(['success' => true, 'searchAssigned' => $searchAssigned], 200);
    }

    public function requestEditedUpload(Request $request, $id)
    {
        $pdf = ModelsRequest::findOrFail($id);

        // Validate the input
        $request->validate([
            'filepdf' => 'required|file|mimes:pdf|max:2048', // Corrected validation rule for PDF files
        ]);

        $oldPDF = $pdf->editor_uploaded_file;

        // Store the new PDF if provided
        if ($request->hasFile('filepdf')) {
            $newPDF = $request->file('filepdf');

            // Calculate the hash of the new PDF content
            $newPDFHash = md5(file_get_contents($newPDF->getRealPath()));

            // Calculate the hash of the old PDF content
            if ($oldPDF && Storage::exists('public/published_journals/' . $oldPDF)) {
                $oldPDFPath = Storage::path('public/published_journals/' . $oldPDF);
                $oldPDFHash = md5(file_get_contents($oldPDFPath));
            }

            // If the hash of the new PDF is different from the old one, store the new PDF
            if (!isset($oldPDFHash) || $newPDFHash !== $oldPDFHash) {
                if ($oldPDF && Storage::exists('public/published_journals/' . $oldPDF)) {
                    Storage::delete('public/published_journals/' . $oldPDF);
                }

                // Use the original name of the uploaded PDF file as the new filename
                $filename = $newPDF->getClientOriginalName();

                // Store the PDF in the 'public' disk (you can configure other disks in config/filesystems.php)
                $newPDF->storeAs('public/published_journals', $filename);
            } else {
                // If the new PDF is the same as the old one, keep the old PDF
                $filename = $oldPDF;
            }
        } else {
            // If 'filepdf' field is not present in the request, keep the old PDF
            $filename = $oldPDF;
        }

        $pdf->update(['editor_uploaded_file' => $filename]);

        $status = 0;
        $published_editor = ModelsRequest::findOrfail($id);
        $published_editor->update([
            'release_status' => $status,
            'edited_upload' => 0,
            'progress_status'=> '3',
            'date_of_published' => now(),
        ]);
        $notification = Notification::create([
            'payment_id' =>  $published_editor->payment_id,
            'client_id' =>  $published_editor->client_id,
            'request_id'=> $id,
            'published' => 0,
            'time' => now(),

        ]);
        $notification = AdminNotification::create([
            'payment_id' => $published_editor->payment_id,
            'request_id' => $id,
            'client_id' =>  $published_editor->client_id,
            'editor_id' =>  $published_editor->editor_id,
            'published' => 0,
            'time' => now(),

        ]);
      
   
        return response()->json(['success' => true, 'message' => 'File uploaded successfully', 'file' => $pdf]);
    }// public function requestEditedUpload(Request $request)


    public function approvedAssignedDocs($id) {
        $status = 'Approved';
        $status_editor = ModelsRequest::findOrfail($id);
        $status_editor->update([
            'editor_status' => $status,
            'progress_status'=> '2',
        ]);
      
        $notification = AdminNotification::create([
            'payment_id' => $status_editor->payment_id,
            'request_id' => $id,
            'client_id' =>  $status_editor->client_id,
            'editor_id' => $status_editor->editor_id,
            'time' => now(),

        ]);


        return response()->json(['success' => true, 'status' => $status_editor,'notification' => $notification],200);
    }//public function approvedAssignedDocs($id) {

    public function rejectedAssignedDocs($id) {
        $reasons = request('reasons');
        $status = 'Rejected';
        $request_status = 'Pending';
        $rejectedAssigned = ModelsRequest::findOrfail($id);
        $rejectedAssigned->update([
            'request_status' => $request_status,
            'editor_status' => $status,
            'reasons' => $reasons,
        ]);
        $notification = AdminNotification::create([
            'payment_id' => $rejectedAssigned->payment_id,
            'request_id' => $id,
            'client_id' =>  $rejectedAssigned->client_id,
            'editor_id' => $rejectedAssigned->editor_id,
            'time' => now(),

        ]);

        return response()->json(['success' => true, 'status' => $rejectedAssigned,'notification' => $notification]);
    }//public function rejectedAssignedDocs($id) {


    //EDITED UPLOADED JOURNAL

    public function getEditedData($id)
    {
        $token = request('token');
        $editedData = ModelsRequest::where('editor_id', $id)->where('request_status', 'Approved')->where('editor_status', 'Approved')->where('release_status', 1)->where('edited_upload', 1)->with(['client' => function($query) {
            $query->select('*');
        }])->with(['payment' => function($query) {
            $query->select('*');
        }])->with(['editor' => function($query) {
            $query->select('*');
        }])->latest()->get();

        return response()->json([
            'success' => true,
            'editedData' => $editedData,
        ], 200);
    }// public function getEditedData()

    public function searchEditedList($id)
    {
        $token = request('token');
        $searchQuery = request('query');
        $formattedSearchQuery = date('Y-m-d', strtotime($searchQuery));
    
        $searchEdited = ModelsRequest::where('editor_id', $id)->where('release_status', 1)->where('edited_upload', 1)
            ->with(['client', 'payment', 'editor'])
            ->where(function ($query) use ($formattedSearchQuery, $searchQuery) {
                $query->where('uploaded_file', 'LIKE', "%{$searchQuery}%")
                    ->orWhere('request_status', 'LIKE', "%{$searchQuery}%")
                    ->orWhereDate('date_time_requests', $formattedSearchQuery)
                    ->orWhereHas('client', function ($query) use ($searchQuery) {
                        $query->whereRaw("CONCAT(firstname, ' ', COALESCE(middlename, ''), ' ', lastname) LIKE ?", ["%{$searchQuery}%"]);
                    });
            })
            ->where('request_status', 'Approved')->where('editor_status', 'Approved')
            ->latest()->get();
    
        return response()->json(['success' => true, 'searchEdited' => $searchEdited], 200);
    }//public function searchEditedList($id)

    public function publishedJournal($id)
    {
        $status = 0;
        $published_editor = ModelsRequest::findOrfail($id);
        $published_editor->update([
            'release_status' => $status,
            'edited_upload' => 0,
        ]);
        
  
        return response()->json(['success' => true, 'published_editor' => $published_editor], 200);
    }//public function publishedJournal($id)


    //PUBLISHED 
    public function getPublishedData($id)
    {
        $token = request('token');
        $publishedData = ModelsRequest::where('editor_id', $id)->where('request_status', 'Approved')->where('editor_status', 'Approved')->where('release_status', 0)->where('edited_upload', 0)->with(['client' => function($query) {
            $query->select('*');
        }])->with(['payment' => function($query) {
            $query->select('*');
        }])->with(['editor' => function($query) {
            $query->select('*');
        }])->latest()->get();

        return response()->json([
            'success' => true,
            'publisheddata' => $publishedData,
        ], 200);
    }// public function getPublishedData()

    public function searchPublishedList($id)
    {
        $searchQuery = request('query');
        $formattedSearchQuery = date('Y-m-d', strtotime($searchQuery));
    
        $searchPublished = ModelsRequest::where('editor_id', $id)->where('release_status', 0)->where('edited_upload', 0)
            ->with(['client', 'payment', 'editor'])
            ->where(function ($query) use ($formattedSearchQuery, $searchQuery) {
                $query->where('editor_uploaded_file', 'LIKE', "%{$searchQuery}%")
                    ->orWhere('request_status', 'LIKE', "%{$searchQuery}%")
                    ->orWhereDate('updated_at', $formattedSearchQuery)
                    ->orWhereHas('client', function ($query) use ($searchQuery) {
                        $query->whereRaw("CONCAT(firstname, ' ', COALESCE(middlename, ''), ' ', lastname) LIKE ?", ["%{$searchQuery}%"]);
                    });
            })
            ->where('request_status', 'Approved')->where('editor_status', 'Approved')
            ->latest()->get();
    
        return response()->json(['success' => true, 'searchPublished' => $searchPublished], 200);
    }//public function searchPublishedList($id)
    
}
