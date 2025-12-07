<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'board_id',
        'user_id',
        'model_type',
        'model_id',
        'action_type',
        'description',
    ];

    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the model that this activity is related to
     */
    public function model()
    {
        return $this->morphTo('model', 'model_type', 'model_id');
    }

    /**
     * Create an activity log entry
     */
    public static function log($boardId, $modelType, $modelId, $actionType, $description)
    {
        return self::create([
            'board_id' => $boardId,
            'user_id' => auth()->id(),
            'model_type' => $modelType,
            'model_id' => $modelId,
            'action_type' => $actionType,
            'description' => $description,
        ]);
    }
}