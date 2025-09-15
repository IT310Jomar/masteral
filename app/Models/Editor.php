<?php

namespace App\Models;

use App\Models\AdminNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Editor extends Model
{
    use HasFactory;

    protected $table = 'editors';
    protected $fillable = ['id','user_id','lastname','firstname','middlename','contact','status','reset_password_status','image'];

    public function users(){
        return $this->belongsTo(User::class,'user_id');
    }
    public function notif(){
        return $this->hasMany(AdminNotification::class,);
    }
}
