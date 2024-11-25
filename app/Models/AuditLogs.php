<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditLogs extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'audit_logs';

    protected $fillable = [
        'revision_number',
        'project_id',
        'user_id',
        'table_name',
        'object_id',
        'action',
        'column_name',
        'state_before',
        'state_after',
        'url',
        'headers',
        'request_body',
        'ip',
        'created_by',
        'updated_by',
        'deleted_by',
        'created_at',
        'updated_at'
    ];

    public static function getFirstImportedOn($projectId){
        return self::select('created_at')->where('project_id',$projectId)->orderBy('id','ASC')->first()->toArray();
    }
}
