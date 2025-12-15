<div class="activity-timeline-container">
    <h3 class="timeline-header">Activity History</h3>
    
    <div class="timeline-list">
        @forelse($activityLogs as $log)
            <div class="timeline-item">
                <div class="timeline-marker"></div>
                <div class="timeline-content">
                    <div class="timeline-top">
                        <span class="timeline-user">
                            {{ $log->user ? $log->user->name : 'System' }}
                        </span>
                        <span class="timeline-time">
                            {{ $log->created_at->diffForHumans() }}
                        </span>
                    </div>
                    <div class="timeline-message">
                        {{ $log->description }}
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-timeline">
                <span class="empty-icon">📝</span>
                <p>No activity recorded yet.</p>
            </div>
        @endforelse
    </div>

    <style>
        .activity-timeline-container {
            padding: 1rem 0;
            color: #c9d1d9;
        }
        
        .timeline-header {
            font-size: 0.9rem;
            font-weight: 600;
            color: #8b949e;
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .timeline-list {
            position: relative;
            padding-left: 1rem;
            border-left: 2px solid #30363d;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 1.5rem;
            padding-left: 1.5rem;
        }

        .timeline-item:last-child {
            margin-bottom: 0;
        }

        .timeline-marker {
            position: absolute;
            left: -5px; /* (2px border / 2) - (8px marker / 2) */
            top: 4px;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #58a6ff;
            border: 2px solid #0d1117; /* Matches dark bg */
        }

        .timeline-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.25rem;
        }

        .timeline-user {
            font-weight: 600;
            font-size: 0.85rem;
            color: #f0f6fc;
        }

        .timeline-time {
            font-size: 0.75rem;
            color: #8b949e;
        }

        .timeline-message {
            font-size: 0.85rem;
            color: #c9d1d9;
            line-height: 1.4;
        }

        .empty-timeline {
            text-align: center;
            padding: 2rem;
            color: #8b949e;
            font-size: 0.85rem;
            border: 1px dashed #30363d;
            border-radius: 6px;
        }
        
        .empty-icon {
            display: block;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            opacity: 0.7;
        }
    </style>
</div>
