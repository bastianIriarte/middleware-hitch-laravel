<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Resource extends Model
{
    use HasFactory;

    protected $table = 'resources';

    protected $fillable = [
        'name',
        'slug',
        'integrations_table',
        'status', 
        'show_user',
        'user_created',
        'created_at',
        'user_updated',
        'updated_at',
        'user_deleted',
        'deleted',
        'deleted_at'
    ];

    public function integrations()
    {
        if (!$this->integration_table) {
        return collect(); // o null
    }

        return (new Integration)
            ->setTableName($this->integration_table)
            ->get();
    }

    public function integrationCounts()
    {
        if (!$this->integrations_table) {
            return (object)[
                'pending' => 0,
                'in_progress' => 0,
                'success' => 0,
                'failed' => 0,
            ];
        }

        $table = $this->integrations_table;

        $results = DB::table($table)
            ->select('status_integration_id', DB::raw('COUNT(*) as total'))
            ->groupBy('status_integration_id')
            ->pluck('total', 'status_integration_id');

        return (object)[
            'pending'     => $results[1] ?? 0,
            'in_progress' => $results[2] ?? 0,
            'success'     => $results[3] ?? 0,
            'failed'      => $results[4] ?? 0,
        ];
    }

}
