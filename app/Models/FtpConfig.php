<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\EncryptionService;

class FtpConfig extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ftp_configs';

    protected $fillable = [
        'company_id',
        'host',
        'port',
        'username',
        'password',
        'root_path',
        'ssl',
        'passive',
        'timeout',
        'status',
        'user_created',
        'user_updated',
        'user_deleted',
    ];

    protected $casts = [
        'port' => 'integer',
        'ssl' => 'boolean',
        'passive' => 'boolean',
        'timeout' => 'integer',
        'status' => 'boolean',
    ];

    protected $hidden = [
        'password',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function setPasswordAttribute($value)
    {
        $encryptionService = app(EncryptionService::class);
        $this->attributes['password'] = $encryptionService->encrypt($value);
    }

    public function getPasswordAttribute($value)
    {
        $encryptionService = app(EncryptionService::class);
        return $encryptionService->decrypt($value);
    }

    public function createdBy()
    {
        return $this->belongsTo(Users::class, 'user_created');
    }

    public function updatedBy()
    {
        return $this->belongsTo(Users::class, 'user_updated');
    }

    public function deletedBy()
    {
        return $this->belongsTo(Users::class, 'user_deleted');
    }
}
