<?php

namespace Webkul\Lead\Repositories;

use Webkul\Core\Eloquent\Repository;

class LeadStatusHistoryRepository extends Repository
{
    /**
     * Specify model class name.
     *
     * @return mixed
     */
    public function model()
    {
        return 'Webkul\Lead\Contracts\LeadStatusHistory';
    }
}
