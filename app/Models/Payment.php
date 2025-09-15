<?php

namespace App\Models;

use App\Models\Client;
use App\Models\Notification;
use App\Models\ModeOfPayment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Request;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';
    protected $fillable = ['id','client_id','service_id','mode_of_payment_id','reference_number','or_number','account','amount','proof_of_payment','date_of_payment','payment_status','upload_status','notes'];

    public function client(){
        return $this->belongsTo(Client::class,'client_id');
    }
    public function modeofpayment(){
        return $this->belongsTo(ModeOfPayment::class,'mode_of_payment_id');
    }
    public function notif(){
        return $this->hasMany(Notification::class,);
    }
    public function request(){
        return $this->hasMany(Request::class);
    }
    public function services(){
        return $this->belongsTo(Services::class,'service_id');
    }
}
