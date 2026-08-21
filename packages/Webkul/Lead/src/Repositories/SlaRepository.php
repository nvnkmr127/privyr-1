<?php

namespace Webkul\Lead\Repositories;

use Webkul\Core\Eloquent\Repository;

class SlaRepository extends Repository
{
    public function model()
    {
        return 'Webkul\Lead\Contracts\Sla';
    }
}
