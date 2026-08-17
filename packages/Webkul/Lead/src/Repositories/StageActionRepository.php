<?php

namespace Webkul\Lead\Repositories;

use Illuminate\Container\Container;
use Webkul\Core\Eloquent\Repository;

class StageActionRepository extends Repository
{
    /**
     * Specify model class name.
     *
     * @return mixed
     */
    public function model()
    {
        return 'Webkul\Lead\Contracts\StageAction';
    }
}
