<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\AdminNotification;
use App\Models\EditorNotification;
use App\Http\Controllers\Controller;

class NotificationController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['readAdminAll','readNotifAdmin','readAll','readNotif','readNotifEditor','readEditor']]);
      
    }
    public function getClientNotification($id)
    {
        $token = request('token');
        $clientNotif = Notification::where('client_id', $id)->with(['request' => function ($q) {
            $q->select('*');
        }])->with(['payment' => function ($q) use ($id) {
            $q->select('*');
        }])->latest()->get();
        $count = Notification::where('is_read', '1')->where('client_id', $id)->count();
        return response()->json(["success" => true, 'count' =>  $count, 'clientNotif' => $clientNotif], 200);
    } // public function getClientNotification($id)

    public function readAll($id)
    {
        $all =  Notification::where('is_read', 1)->where('client_id',$id)->update(['is_read' => '0']);
        return response()->json(["success" => true, 'all' => $all], 200);
    } // public function readAll()

    public function readNotif($id)
    {
        $read =  Notification::findOrFail($id)->update(['is_read' => '0']);
        return response()->json(["success" => true, 'read' => $read], 200);
    } //  public function readNotif()

    public function adminNotification()
    {
        $token = request('token');
        $adminNotif = AdminNotification::with(['request' => function ($q) {
            $q->select('*');
        }])->with(['payment' => function ($q) {
            $q->select('*')->with(['client' => function ($q) {
                $q->select('*');
            }]);
        }])->with(['editor' => function ($q) {
            $q->select('*');
        }])->latest()->get();
        $count = AdminNotification::where('is_read', '1')->count();
        return response()->json(["success" => true, 'count' => $count, 'adminNotif' =>   $adminNotif], 200);
    } // public function adminNotification(){

    public function readAdminAll()
    {
        $all = AdminNotification::where('is_read', 1)->update(['is_read' => '0']);
        return response()->json(["success" => true, 'all' => $all], 200);
    } //  public function readAdminAll(){

    public function readNotifAdmin($id)
    {
        $read =  AdminNotification::findOrFail($id)->update(['is_read' => '0']);
        return response()->json(["success" => true, 'read' => $read], 200);
    } //  public function readAdminAll()

    public function editorNotification($id){
        $token = request('token');
        $editor = EditorNotification::where('editor_id',$id)->with(['request' => function($q){
            $q->select('*');
        }])->latest()->get();
        $count = EditorNotification::where('is_read', '1')->where('editor_id', $id)->count();
        return response()->json(['success' => true,'editor'=>$editor, 'count' => $count],200);
    }// public function editorNotification($id){

    public function readNotifEditor($id)
    {
        $all = EditorNotification::where('is_read', 1)->where('editor_id',$id)->update(['is_read' => '0']);
        return response()->json(["success" => true, 'all' => $all], 200);
    }// public function readNotifEditor($id)

    
    public function readEditor($id)
    {
        $read =  EditorNotification::findOrFail($id)->update(['is_read' => '0']);
        return response()->json(["success" => true, 'read' => $read], 200);
    } //  public function readEditor($id)


}
