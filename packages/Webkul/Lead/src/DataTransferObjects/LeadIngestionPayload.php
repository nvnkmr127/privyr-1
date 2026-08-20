<?php

namespace Webkul\Lead\DataTransferObjects;

class LeadIngestionPayload
{
    public function __construct(
        public readonly string $origin,
        public readonly ?string $sourceName = null,
        public readonly ?int $sourceId = null,
        public readonly ?string $externalId = null,
        public readonly array $leadData = [],
        public readonly array $metadata = [],
        public readonly string $duplicateAction = 'reject',
        public readonly ?int $connectorId = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            origin: $data['origin'] ?? 'manual',
            sourceName: $data['source_name'] ?? null,
            sourceId: $data['source_id'] ?? null,
            externalId: $data['external_id'] ?? null,
            leadData: $data['lead_data'] ?? [],
            metadata: $data['metadata'] ?? [],
            duplicateAction: $data['duplicate_action'] ?? 'reject',
            connectorId: $data['connector_id'] ?? null
        );
    }
}
