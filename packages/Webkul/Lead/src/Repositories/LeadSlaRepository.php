<?php

namespace Webkul\Lead\Repositories;

use Webkul\Core\Eloquent\Repository;

class LeadSlaRepository extends Repository
{
    public function model()
    {
        return 'Webkul\Lead\Contracts\LeadSla';
    }

    public function findDueSoonSlas($dueSoonTime, $now)
    {
        return $this->model
            ->where('status', 'On Track')
            ->where('due_at', '<=', $dueSoonTime)
            ->where('due_at', '>', $now)
            ->get();
    }

    public function findBreachedSlas($now)
    {
        return $this->model
            ->whereIn('status', ['On Track', 'Due Soon'])
            ->where('due_at', '<=', $now)
            ->get();
    }
}
