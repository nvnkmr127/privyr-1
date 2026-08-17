<?php

namespace Webkul\Lead\Repositories;

use Webkul\Core\Eloquent\Repository;

class StageRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'Webkul\Lead\Contracts\Stage';
    }

    public function create(array $data)
    {
        $stage = parent::create($data);

        if (isset($data['actions']) && is_array($data['actions'])) {
            foreach ($data['actions'] as $action) {
                app(StageActionRepository::class)->create(array_merge($action, [
                    'lead_pipeline_stage_id' => $stage->id,
                ]));
            }
        }

        return $stage;
    }

    public function update(array $data, $id, $attribute = 'id')
    {
        $stage = parent::update($data, $id, $attribute);

        if (isset($data['actions']) && is_array($data['actions'])) {
            app(StageActionRepository::class)->deleteWhere(['lead_pipeline_stage_id' => $stage->id]);

            foreach ($data['actions'] as $action) {
                app(StageActionRepository::class)->create(array_merge($action, [
                    'lead_pipeline_stage_id' => $stage->id,
                ]));
            }
        }

        return $stage;
    }
}
