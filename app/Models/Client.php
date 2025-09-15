<?php

namespace App\Models;

use App\Models\User;
use App\Models\SchoolType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Model
{
    use HasFactory;

    protected $table = 'clients';
    protected $fillable = ['id','user_id','lastname','firstname','middlename','contact','course','school','school_type_id','image','status'];

    public function users(){
        return $this->belongsTo(User::class,'user_id');
    }
    public function schoolType(){
        return $this->belongsTo(SchoolType::class,'school_type_id');
    } public function request(){
        return $this->hasMany(Request::class);
    }
}
