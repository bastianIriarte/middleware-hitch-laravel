<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'file_logs';

    protected $fillable = [
        'company_id',
        'file_type_id',
        'original_filename',
        'stored_filename',
        'file_path',
        'file_size',
        'records_count',
        'rejected_count',
        'status',
        'ftp_response',
        'error_message',
        'received_at',
        'uploaded_at',
        'user_created',
        'user_updated',
        'user_deleted',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'records_count' => 'integer',
        'rejected_count' => 'integer',
        'received_at' => 'datetime',
        'uploaded_at' => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function fileType()
    {
        return $this->belongsTo(FileType::class);
    }

    public function errors()
    {
        return $this->hasMany(FileError::class);
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
