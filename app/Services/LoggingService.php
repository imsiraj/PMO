<?php

namespace App\Services;

use App\Models\AuditLogs;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Exception;

class LoggingService
{
    /**
     * Log changes to the audit log.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $action
     * @param string $revisionNumber
     * @param int $projectId
     * @param int|null $userId
     * @return void
     */
    public function logChange($model, $action, $revisionNumber, $projectId, $userId = null, $originalAttributes = null)
    {
        try {
            if ($action == 'created') {
                AuditLogs::create([
                    'revision_number' => $revisionNumber,
                    'project_id' => $projectId,
                    'user_id' => $userId,
                    'table_name' => $model->getTable(),
                    'object_id' => $model->getKey(),
                    'action' => $action,
                    'column_name' => null,
                    'state_before' => null,
                    'state_after' => json_encode($model->getAttributes()),
                    'url' => Request::fullUrl(),
                    'headers' => json_encode(Request::header()),
                    'request_body' => json_encode(Request::all()),
                    'ip' => Request::ip(),
                    'created_by' => $userId,
                    'updated_by' => $userId,
                    'deleted_by' => $userId,
                ]);
            } else {
                $updatedAttributes = $model->getAttributes();
                $changedAttributes = [];
                foreach ($updatedAttributes as $key => $value) {
                    if ($key === 'updated_at' | $key === 'created_at' | $key === 'revision_number') {
                        continue;
                    }
                    if (($originalAttributes[$key] ?? null) !== $value) {
                        $changedAttributes[$key] = $value;

                        AuditLogs::create([
                            'revision_number' => $revisionNumber,
                            'project_id' => $projectId,
                            'user_id' => $userId,
                            'table_name' => $model->getTable(),
                            'object_id' => $model->getKey(),
                            'action' => $action,
                            'column_name' => $key,
                            'state_before' => $originalAttributes[$key] ?? null,
                            'state_after' => $value,
                            'url' => request()->fullUrl(),
                            'headers' => json_encode(request()->header()),
                            'request_body' => json_encode(request()->all()),
                            'ip' => request()->ip(),
                            'created_by' => $userId,
                            'updated_by' => $userId,
                            'deleted_by' => $userId,
                        ]);
                    }
                }
            }
        } catch (Exception $e) {
            Log::error('Error while storing logs: ' . $e->getMessage());
        }
    }
}
