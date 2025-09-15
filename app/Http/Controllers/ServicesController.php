<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ModeOfPayment;
use App\Http\Controllers\Controller;
use App\Models\Services;
use Illuminate\Support\Facades\Storage;

class ServicesController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['addpaymentmethod','edit_paymentmethod','statusPayment','addServices','edit_Services','statusServices']]);
    }

    //PAYMENT METHOD
    public function getAllPaymentMethod()
    {
        $token = request('token');
        $methods = ModeOfPayment::latest()->get();

        return response()->json(["success" => true, 'paymentmethoddata' => $methods, 'token' => $token], 200);
    } //public function getAllPaymentMethod(){

    public function searchPaymentMethods()
    {
        $searchQuery = request('query');
        $token = request('token');

        $searchPayment = ModeOfPayment::where(function ($query) use ($searchQuery) {
            $query->where('name', 'LIKE', "%{$searchQuery}%")
                ->orWhere('status', 'LIKE', "%{$searchQuery}%");
        })->latest()->get();

        return response()->json(['success' => true, 'searchPayment' => $searchPayment], 200);
    } // public function searchPaymentMethods()
    
    
    public function addpaymentmethod(Request $request)
    {
        $filename = null; // Initialize the filename as null

        if ($request->hasFile('qr_code')) {
            $request->validate([
                'qr_code' => 'required|max:5000',
                
            ]);

            $image = $request->file('qr_code');

            $filename = $image->getClientOriginalName();

            // Store the image in the 'public' disk (you can configure other disks in config/filesystems.php)
            $image->storeAs('public/qr_code_image', $filename);
        }

        $addpaymentData = ModeOfPayment::create([
            'name' => request('paymentname'),
            'qr_code_image' => $filename,
            'account' => request('account')
        ]);

        return response()->json(['success' => true, 'addpaymentData' => $addpaymentData], 200);
    } //  public function addpaymentmethod(Request $request)

    public function edit_paymentmethod(Request $request, $id)
    {
        $qrcode = request('qrcode');
        $profile = ModeOfPayment::findOrFail($id);
        $filename = null;
        // Validate the input
        $request->validate([
            'qr_code' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
    
        $oldImage = $profile->qr_code_image;

        if ($qrcode === 'No') {
            // Delete the old image
            Storage::delete('public/qr_code_image/' . $oldImage);
        
            // Set qr_code_image to null
            $profile->qr_code_image = null;
            $profile->account = request('account') ?? null;
            $profile->save();
        } else if($qrcode === 'No' && is_null($oldImage = $profile->qr_code_image) ) {
            $profile->account = request('account') ?? null;
            $profile->save();
        } else {
            // Store the new image if provided
            if ($request->hasFile('qr_code')) {
                $newImagePath = $request->file('qr_code');
        
                // Calculate the hash of the new image content
                $newImageHash = md5(file_get_contents($newImagePath->getRealPath()));
        
                // Calculate the hash of the old image content
                $oldImageHash = $oldImage ? md5(Storage::get('public/qr_code_image/' . $oldImage)) : null;
        
                // If the hash of the new image is different from the old one, store the new image
                if ($newImageHash !== $oldImageHash) {
                    if ($oldImage) {
                        Storage::delete('public/qr_code_image/' . $oldImage);
                    }
        
                    $filename = $newImagePath->getClientOriginalName();
        
                    // Store the image in the 'public' disk
                    $newImagePath->storeAs('public/qr_code_image', $filename);
                } else {
                    // If the new image is the same as the old one, keep the old image
                    $filename = $oldImage;
                }
            } else {
                // If 'qr_code' field is not present in the request, keep the old image
                $filename = $oldImage;
            }
        }
    
        $editPaymentMethod = ModeOfPayment::updateOrCreate(
            ['id' => $id],
            [
                'name' => $request->input('paymentname'),
                'qr_code_image' => $filename,
                'account' => request('account') ?? null
            ]
        );
    
        return response()->json(['success' => true, 'editPaymentMethod' => $editPaymentMethod], 200);
    } //  public function edit_paymentmethod(Request $request, $id)

    public function statusPayment($id)
    {
        $status = request('status');
        $available_payment = ModeOfPayment::findOrfail($id);
        $available_payment->update([
            'status' => $status,
        ]);
        return response()->json(['success' => true, 'available_payment' => $available_payment], 200);
    }//public function availablePayment($id)
    //PAYMENT METHOD END



    //SERVICES METHOD
    public function getAllServices()
    {
        $token = request('token');
        $services = Services::latest()->get();

        return response()->json(["success" => true, 'servicedata' => $services, 'token' => $token], 200);
    } //public function getAllServices(){

    public function searchServices()
    {
        $searchQuery = request('query');
        $token = request('token');
        
        $searchService = Services::where(function ($query) use ($searchQuery) {
            $query->where('name', 'LIKE', "%{$searchQuery}%")
                    ->orWhere('price', 'LIKE', "%{$searchQuery}%")
                    ->orWhere('status', 'LIKE', "%{$searchQuery}%");
        })->latest()->get();

        return response()->json(['success' => true, 'searchService' => $searchService], 200);
    } // public function searchServices()
    
    
    public function addServices(Request $request)
    {
        $addServiceData = Services::create([
            'name' => request('services_name'),
            'price' => request('price'),
        ]);

        return response()->json(['success' => true, 'addServiceData' => $addServiceData], 200);
    } //  public function addServices(Request $request)

    public function edit_Services(Request $request, $id)
    {
        $editServices = Services::updateOrCreate(
            ['id' => $id],
            [
                'name' => request('services_name'),
                'price' => request('price'),
            ]
        );
    
        return response()->json(['success' => true, 'editServices' => $editServices], 200);
    } //  public function edit_Services(Request $request, $id)

    public function statusServices($id)
    {
        $status = request('status');
        $available_services = Services::findOrfail($id);
        $available_services->update([
            'status' => $status,
        ]);
        return response()->json(['success' => true, 'available_services' => $available_services], 200);
    }//public function statusServices($id)
    //SERVICES END
}
