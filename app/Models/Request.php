<?php

namespace App\Models;

use App\Models\Client;
use App\Models\Editor;
use App\Models\Payment;
use App\Models\Notification;
use App\Models\EditorNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Request extends Model
{
    use HasFactory;

    protected $table = 'requests';
    protected $fillable = ['id','client_id','payment_id','editor_id','date_time_requests','request_status','editor_status','progress_status','release_status','uploaded_file','editor_uploaded_file','edited_upload','date_of_published','notes','reasons'];

    public function client(){
        return $this->belongsTo(Client::class,'client_id');
    }
    public function payment(){
        return $this->belongsTo(Payment::class,'payment_id');
    }
    public function editor(){
        return $this->belongsTo(Editor::class,'editor_id');
    }
    public function notif(){
        return $this->hasMany(Notification::class);
    }
    public function editorNotif(){
        return $this->hasMany(EditorNotification::class);
    }
}
