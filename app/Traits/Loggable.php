<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait Loggable
{
    protected static function bootLoggable()
    {
        static::created(function ($model) {
            if (!$model->shouldLogActivity())
                return;

            $desc = method_exists($model, 'customCreatedDescription')
                ? $model->customCreatedDescription()
                : "Created " . class_basename($model) . ": " . $model->getLogIdentifier();

            $model->createLog('created', $desc);
        });

        static::updated(function ($model) {
            if (!$model->shouldLogActivity())
                return;

            $changes = $model->getLogChanges();
            if (empty($changes))
                return;

            $desc = method_exists($model, 'customUpdatedDescription')
                ? $model->customUpdatedDescription($changes)
                : $model->buildUpdateDescription($changes);

            $model->createLog('updated', $desc);
        });

        static::deleting(function ($model) {
            // Use "deleting" event instead of "deleted" - runs BEFORE deletion
            if (!$model->shouldLogActivity())
                return;

            $desc = method_exists($model, 'customDeletedDescription')
                ? $model->customDeletedDescription()
                : "Deleted " . class_basename($model) . ": " . $model->getLogIdentifier();

            $model->createLog('deleted', $desc);
        });
    }

    public function createLog($action, $description)
    {
        $boardId = $this->getLogBoardId();
        if (!$boardId || !auth()->check())
            return;

        return ActivityLog::create([
            'board_id' => $boardId,
            'user_id' => auth()->id(),
            'model_type' => get_class($this),
            'model_id' => $this->id,
            'action_type' => $action,
            'description' => $description,
        ]);
    }

    protected function shouldLogActivity()
    {
        return auth()->check() && $this->getLogBoardId();
    }

    protected function getLogBoardId()
    {
        if (isset($this->board_id))
            return $this->board_id;
        if ($this instanceof \App\Models\Board)
            return $this->id;
        if (method_exists($this, 'board') && $this->board)
            return $this->board->id;
        return null;
    }

    protected function getLogChanges()
    {
        $loggable = method_exists($this, 'getLoggableFields')
            ? $this->getLoggableFields()
            : array_diff(array_keys($this->getAttributes()), ['created_at', 'updated_at', 'deleted_at', 'remember_token', 'order']);

        $changes = [];
        foreach ($loggable as $attr) {
            if ($this->wasChanged($attr)) {
                $changes[$attr] = [
                    'old' => $this->getOriginal($attr),
                    'new' => $this->getAttribute($attr),
                ];
            }
        }
        return $changes;
    }

    protected function getLogIdentifier()
    {
        return $this->title ?? $this->name ?? $this->id ?? 'Unknown';
    }

    protected function buildUpdateDescription($changes)
    {
        $items = [];
        foreach ($changes as $attr => $vals) {
            $label = ucfirst(str_replace('_', ' ', $attr));
            $old = $this->formatLogValue($attr, $vals['old']);
            $new = $this->formatLogValue($attr, $vals['new']);
            $items[] = "{$label}: '{$old}' → '{$new}'";
        }
        return "Updated " . class_basename($this) . " '" . $this->getLogIdentifier() . "': " . implode(', ', $items);
    }

    protected function formatLogValue($attr, $val)
    {
        if (is_null($val))
            return 'none';
        if (is_bool($val))
            return $val ? 'yes' : 'no';
        if ($val instanceof \Carbon\Carbon)
            return $val->format('M d, Y');
        if (is_array($val))
            return json_encode($val);

        $str = (string) $val;
        return strlen($str) > 50 ? substr($str, 0, 47) . '...' : $str;
    }
}