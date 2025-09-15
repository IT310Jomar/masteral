<?php

namespace App\Models;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;
    protected $table = 'notifications';
    protected $fillable = ['payment_id','client_id','time','is_read','request_id','published'];


    public function payment(){
        return $this->belongsTo(Payment::class,'payment_id');
    }
    public function request(){
        return $this->belongsTo(Request::class,'request_id');
    }
}
