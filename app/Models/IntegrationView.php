<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrationView extends Model
{
    use HasFactory;

    protected $table = 'integrations_view';

    public $timestamps = false;
    // No usaremos auto-increment en vista
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        // Columna añadida en la vista para identificar la tabla origen
        'resource',

        // Columnas heredadas de las tablas sap_integrations_*
        'id',
        'origin',
        'destiny',
        'create_body',
        'request_body',
        'code',
        'message',
        'response',
        'status_integration_id',
        'user_created',
        'created_at',
        'user_updated',
        'updated_at',
        'user_deleted',
        'deleted',
        'deleted_at',
    ];

    public function status()
    {
        return $this->hasOne('App\Models\StatusIntegration', 'id', 'status_integration_id');
    }

    // Relaciones con el modelo Users
    public function createdBy()
    {
        return $this->belongsTo('App\Models\Users', 'user_created');
    }

    public function updatedBy()
    {
        return $this->belongsTo('App\Models\Users', 'user_updated');
    }

    public function deletedBy()
    {
        return $this->belongsTo('App\Models\Users', 'user_deleted');
    }
}
