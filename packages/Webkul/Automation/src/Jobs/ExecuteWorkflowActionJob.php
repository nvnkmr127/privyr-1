<?php

namespace Webkul\Automation\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webkul\Automation\Models\WorkflowExecutionProxy;

class ExecuteWorkflowActionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $workflow;

    public $entity;

    public $eventName;

    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @param  mixed  $workflow
     * @param  mixed  $entity
     * @param  string  $eventName
     */
    public function __construct($workflow, $entity, $eventName)
    {
        $this->workflow = $workflow;
        $this->entity = $entity;
        $this->eventName = $eventName;
    }

    /**
     * @var int
     */
    public static $executionDepth = 0;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Check for idempotency
        $idempotencyKey = $this->generateIdempotencyKey();

        $execution = WorkflowExecutionProxy::firstOrCreate(
            ['idempotency_key' => $idempotencyKey],
            [
                'workflow_id' => $this->workflow->id,
                'entity_type' => $this->workflow->entity_type,
                'entity_id' => $this->entity->id,
                'trigger' => $this->eventName,
                'status' => 'pending',
                'started_at' => now(),
            ]
        );

        // If execution is already completed or pending, it's a duplicate event, exit early.
        if (in_array($execution->status, ['completed', 'pending']) && ! $execution->wasRecentlyCreated) {
            return;
        }

        // Execution depth for loop prevention
        if (self::$executionDepth > 3) {
            $execution->update([
                'status' => 'failed',
                'error' => 'Automation loop detected (execution depth > 3).',
                'completed_at' => now(),
            ]);

            return;
        }
        
        self::$executionDepth++;

        try {
            $workflowEntity = app(config('workflows.trigger_entities.'.$this->workflow->entity_type.'.class'));

            // Execute the actions
            $workflowEntity->executeActions($this->workflow, $this->entity);

            $execution->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
        } catch (Exception $e) {
            $execution->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
                'retry_count' => $this->attempts(),
                'completed_at' => now(),
            ]);

            throw $e;
        } finally {
            self::$executionDepth--;
        }
    }

    /**
     * Generate unique idempotency key based on workflow and entity
     */
    protected function generateIdempotencyKey()
    {
        return md5($this->workflow->id.'_'.$this->entity->id.'_'.$this->eventName);
    }
}
