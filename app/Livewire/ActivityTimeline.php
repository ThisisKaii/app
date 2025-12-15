<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityTimeline extends Component
{
    public $activityLogs;
    public $modelId;
    public $modelType;

    public function mount(Model $model)
    {
        $this->modelId = $model->id;
        $this->modelType = get_class($model);
        $this->loadLogs();
    }

    public function loadLogs()
    {
        $this->activityLogs = ActivityLog::where('model_id', $this->modelId)
            ->where('model_type', $this->modelType)
            ->with('user')
            ->latest()
            ->get();
    }

    public function render()
    {
        return view('livewire.activity-timeline');
    }
}
