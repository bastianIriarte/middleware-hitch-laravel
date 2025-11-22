<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResetPassword extends Model
{
    protected $table = 'reset_password';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'email',
        'token',
        'change_date',
        'expiration_date',
        'created_at',
        'updated_at',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo('App\Models\Users', 'user_id');
    }
}
