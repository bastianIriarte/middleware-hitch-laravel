<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileError extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'file_errors';

    protected $fillable = [
        'company_id',
        'file_type_id',
        'file_log_id',
        'error_type',
        'error_message',
        'error_details',
        'line_number',
        'record_data',
        'severity',
        'user_created',
        'user_updated',
        'user_deleted',
    ];

    protected $casts = [
        'line_number' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function fileType()
    {
        return $this->belongsTo(FileType::class);
    }

    public function fileLog()
    {
        return $this->belongsTo(FileLog::class);
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
