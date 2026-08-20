<?php

namespace Webkul\Lead\Contracts;

use Webkul\Lead\DataTransferObjects\LeadIngestionPayload;

interface LeadIngestionService
{
    /**
     * Ingest a lead from any origin.
     *
     * @param LeadIngestionPayload $payload
     * @return \Webkul\Lead\Contracts\Lead
     * @throws \Webkul\Lead\Exceptions\LeadIngestionException
     */
    public function ingest(LeadIngestionPayload $payload);
}
