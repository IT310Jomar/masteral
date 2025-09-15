<?php

namespace App\Models;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sales extends Model
{
    use HasFactory;

    protected $table = 'sales';
    protected $fillable = ['id','payment_id'];

    public function payment(){
        return $this->belongsTo(Payment::class,'payment_id');
    }
}
