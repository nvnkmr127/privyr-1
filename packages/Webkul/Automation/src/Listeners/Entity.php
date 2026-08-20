<?php

namespace Webkul\Automation\Listeners;

use Webkul\Automation\Helpers\Validator;
use Webkul\Automation\Repositories\WorkflowRepository;

class Entity
{
    /**
     * Create a new repository instance.
     *
     * @return void
     */
    public function __construct(
        protected WorkflowRepository $workflowRepository,
        protected Validator $validator
    ) {}

    /**
     * @param  string  $eventName
     * @param  mixed  $entity
     * @return void
     */
    public function process($eventName, $entity)
    {
        $workflows = $this->workflowRepository->findWhere([
            'event' => $eventName,
            'status' => 'active'
        ]);

        foreach ($workflows as $workflow) {
            $workflowEntity = app(config('workflows.trigger_entities.'.$workflow->entity_type.'.class'));

            $entity = $workflowEntity->getEntity($entity);

            if (! $this->validator->validate($workflow, $entity)) {
                continue;
            }

            // Dispatch job instead of executing synchronously
            \Webkul\Automation\Jobs\ExecuteWorkflowActionJob::dispatch($workflow, $entity, $eventName);
        }
    }
}
