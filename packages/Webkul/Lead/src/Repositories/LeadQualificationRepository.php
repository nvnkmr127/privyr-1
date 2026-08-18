<?php

namespace Webkul\Lead\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Lead\Contracts\LeadQualification;

class LeadQualificationRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public function model()
    {
        return LeadQualification::class;
    }
}
