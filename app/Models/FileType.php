<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileType extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'file_types';

    protected $fillable = [
        'code',
        'name',
        'description',
        'file_extension',
        'status',
        'user_created',
        'user_updated',
        'user_deleted',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function fileLogs()
    {
        return $this->hasMany(FileLog::class);
    }

    public function fileErrors()
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
