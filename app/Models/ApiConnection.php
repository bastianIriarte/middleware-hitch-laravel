<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiConnection extends Model
{
    use HasFactory;

     protected $table = 'api_connections';

    protected $fillable = [
        'software',
        'endpoint',
        'port',
        'database',
        'username',
        'password',
        'api_key',
        'status',
        'user_created',
        'created_at',
        'user_updated',
        'updated_at',
        'user_deleted',
        'deleted',
        'deleted_at',
    ];
}
