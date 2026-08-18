<?php

namespace Webkul\Lead\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Lead\Contracts\LeadAssignment;

class LeadAssignmentRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return LeadAssignment::class;
    }
}
