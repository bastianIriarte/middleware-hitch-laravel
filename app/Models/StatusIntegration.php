<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusIntegration extends Model
{
    use HasFactory;

    protected $table = 'status_integrations';

    protected $fillable = [
        'status',
        'description',
        'badge',
        'icon',
        'user_created',
        'user_updated',
        'user_deleted',
        'deleted',
        'deleted_at',
    ];

    public function status()
    {
        return $this->hasOne('App\Models\StatusIntegration', 'id', 'status_integration_id');
    }
}
