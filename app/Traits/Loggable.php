<?php

namespace App\Traits;

use App\Models\ActivityLog;

/**
 * Automatically logs model lifecycle events (created, updated, deleted) to the ActivityLog.
 * 
 * Models using this trait can customize log descriptions by implementing:
 * - customCreatedDescription(): string
 * - customUpdatedDescription(array $changes): string
 * - customDeletedDescription(): string
 * - getLoggableFields(): array (to limit which fields trigger update logs)
 */
trait Loggable
{
    /**
     * Boot the trait and register model event listeners for activity logging.
     */
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
            if (!$model->shouldLogActivity())
                return;

            $desc = method_exists($model, 'customDeletedDescription')
                ? $model->customDeletedDescription()
                : "Deleted " . class_basename($model) . ": " . $model->getLogIdentifier();

            $model->createLog('deleted', $desc);
        });
    }

    /**
     * Create an activity log entry for this model.
     *
     * @param string $action The action type (created, updated, deleted)
     * @param string $description Human-readable description of the change
     * @return ActivityLog|null The created log entry, or null if logging conditions aren't met
     */
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

    /**
     * Check if this model change should be logged (requires authenticated user and board context).
     *
     * @return bool
     */
    protected function shouldLogActivity()
    {
        return auth()->check() && $this->getLogBoardId();
    }

    /**
     * Resolve the board_id for this model (direct property, self if Board, or via relationship).
     *
     * @return int|null
     */
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

    /**
     * Get the changed attributes that should be logged, excluding timestamps and system fields.
     *
     * @return array Associative array of changed fields with 'old' and 'new' values
     */
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

    /**
     * Get a human-readable identifier for this model (title, name, or id).
     *
     * @return string
     */
    protected function getLogIdentifier()
    {
        return $this->title ?? $this->name ?? $this->id ?? 'Unknown';
    }

    /**
     * Build a descriptive update message listing all changed fields.
     *
     * @param array $changes The changed fields from getLogChanges()
     * @return string Formatted description like "Updated Task 'Title': Status: 'old' → 'new'"
     */
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

    /**
     * Format a value for display in log descriptions, handling nulls, booleans, dates, and long strings.
     *
     * @param string $attr The attribute name (for potential type-specific formatting)
     * @param mixed $val The value to format
     * @return string Human-readable representation
     */
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