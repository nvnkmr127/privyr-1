<?php

use Webkul\Lead\Models\Lead;
use Webkul\User\Models\User;
use Webkul\DataTransfer\Models\Import;
use Webkul\DataTransfer\Models\ImportBatch;
use Webkul\Lead\Services\LeadImportService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Webkul\Lead\Jobs\LeadIngestionBatch;

uses(\Tests\TestCase::class);

beforeEach(function () {
    $this->user = User::factory()->create(['role_id' => 1]); // Admin
    $this->actingAs($this->user, 'user');
    
    // Create a dummy CSV file
    $this->csvHeader = "title,emails,contact_numbers\n";
    $this->csvRow1 = "Test Lead 1,test1@example.com,1234567890\n";
    $this->csvRow2 = "Test Lead 2,test2@example.com,0987654321\n";
    
    $this->csvContent = $this->csvHeader . $this->csvRow1 . $this->csvRow2;
    Storage::fake('local');
    Storage::disk('local')->put('imports/test.csv', $this->csvContent);
});

it('can analyze a csv file and return headers', function () {
    $service = app(LeadImportService::class);
    $result = $service->analyzeFile(storage_path('app/imports/test.csv'));
    
    expect($result['headers'])->toContain('title', 'emails', 'contact_numbers');
    expect(count($result['preview_rows']))->toBe(2);
    expect($result['preview_rows'][0]['title'])->toBe('Test Lead 1');
});

it('can detect duplicates during mapping validation', function () {
    // Create an existing lead matching the CSV
    Lead::factory()->create([
        'emails' => json_encode([['label' => 'work', 'value' => 'test1@example.com']])
    ]);

    $service = app(LeadImportService::class);
    $mapping = [
        'title' => 'title',
        'emails' => 'emails'
    ];
    
    $result = $service->validateMapping(storage_path('app/imports/test.csv'), $mapping, []);
    
    expect($result['valid'])->toBeTrue();
    expect($result['total_scanned'])->toBe(2);
    expect($result['duplicates_detected'])->toBe(1);
});

it('dispatches the ingestion batch job', function () {
    Queue::fake();

    $service = app(LeadImportService::class);
    $mapping = [
        'title' => 'title',
        'emails' => 'emails'
    ];
    $settings = ['duplicate_action' => 'skip'];
    
    $importId = $service->dispatchImportJob(storage_path('app/imports/test.csv'), $mapping, $settings, $this->user->id);
    
    expect($importId)->toBeGreaterThan(0);
    
    $import = Import::find($importId);
    expect($import->state)->toBe(Import::STATE_PENDING);
    
    Queue::assertPushed(LeadIngestionBatch::class);
});

it('processes batches through lead ingestion service', function () {
    // Actually run the job logic
    $service = app(LeadImportService::class);
    $mapping = [
        'title' => 'title',
        'emails' => 'emails'
    ];
    $settings = ['duplicate_action' => 'skip'];
    
    $importId = $service->dispatchImportJob(storage_path('app/imports/test.csv'), $mapping, $settings, $this->user->id);
    
    $job = new LeadIngestionBatch($importId);
    $job->handle(
        app(\Webkul\DataTransfer\Repositories\ImportRepository::class),
        app(\Webkul\DataTransfer\Repositories\ImportBatchRepository::class),
        app(\Webkul\Lead\Services\LeadIngestionService::class)
    );
    
    $import = Import::find($importId);
    expect($import->state)->toBe(Import::STATE_PROCESSED);
    expect($import->summary['created'])->toBe(2);
    
    $leads = Lead::whereIn('title', ['Test Lead 1', 'Test Lead 2'])->get();
    expect($leads->count())->toBe(2);
});
