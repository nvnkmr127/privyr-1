<?php

namespace Webkul\Lead\Contracts;

use Webkul\Lead\DataTransferObjects\LeadIngestionPayload;
use Webkul\Lead\Exceptions\LeadIngestionException;

interface LeadIngestionService
{
    /**
     * Ingest a lead from any origin.
     *
     * @return Lead
     *
     * @throws LeadIngestionException
     */
    public function ingest(LeadIngestionPayload $payload);
}
