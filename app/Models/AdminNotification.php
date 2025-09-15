<?php

namespace App\Models;

use App\Models\Editor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdminNotification extends Model
{
    use HasFactory;
    protected $table = 'admin_notification';
    protected $fillable = ['payment_id','client_id','editor_id','time','is_read','request_id','published'];


    public function payment(){
        return $this->belongsTo(Payment::class,'payment_id');
    }
    public function request(){
        return $this->belongsTo(Request::class,'request_id');
    }
    public function editor(){
        return $this->belongsTo(Editor::class,'editor_id');
    }
}
