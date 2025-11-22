<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Integration extends Model
{
   use HasFactory;

    public function setTableName($table)
    {
        $this->setTable($table);
        return $this;
    }

    protected $fillable = [
        'service_name',
        'origin',
        'destiny',
        'create_body',
        'request_body',
        'code',
        'message',
        'response',
        'status_integration_id',
        'attempts',
        'caller_method',
        'includes_wms_integration',
        'wms_request_body',
        'wms_code',
        'wms_response',
        'entry_request_body',
        'entry_response',
        'user_created',
        'created_at',
        'user_updated',
        'updated_at',
        'user_deleted',
        'deleted',
        'deleted_at',
    ];
}
