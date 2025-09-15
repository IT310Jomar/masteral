<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EditorNotification extends Model
{
    use HasFactory;
    protected $table = 'editor_notification';
    protected $fillable = ['payment_id','client_id','time','is_read','request_id','editor_id'];


    public function payment(){
        return $this->belongsTo(Payment::class,'payment_id');
    }
    public function request(){
        return $this->belongsTo(Request::class,'request_id');
    }
}
