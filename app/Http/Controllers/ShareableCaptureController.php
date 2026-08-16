<?php

namespace App\Http\Controllers;

use App\Services\LeadDistributionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Webkul\Contact\Repositories\PersonRepository;
use Webkul\Lead\Repositories\LeadRepository;
use Webkul\Lead\Repositories\PipelineRepository;
use Webkul\Lead\Repositories\SourceRepository;

class ShareableCaptureController extends Controller
{
    public function __construct(
        protected LeadRepository $leadRepository,
        protected PersonRepository $personRepository,
        protected SourceRepository $sourceRepository,
        protected PipelineRepository $pipelineRepository,
        protected LeadDistributionService $distributionService
    ) {}

    /**
     * Display public shareable lead capture form.
     */
    public function show($token = 'default')
    {
        return response()->make('
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Contact Us - Get In Touch</title>
                <style>
                    body { font-family: system-ui, -apple-system, sans-serif; background: #f1f5f9; margin: 0; padding: 20px; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
                    .card { background: white; border-radius: 16px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); padding: 32px; width: 100%; max-width: 440px; }
                    h2 { margin-top: 0; font-size: 22px; color: #0f172a; }
                    p { color: #64748b; font-size: 14px; margin-bottom: 24px; }
                    .form-group { margin-bottom: 16px; text-align: left; }
                    label { display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px; }
                    input, textarea { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; box-sizing: border-box; font-size: 14px; }
                    input:focus, textarea:focus { border-color: #2563eb; outline: none; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
                    button { width: 100%; background: #2563eb; color: white; border: none; padding: 14px; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; transition: background 0.2s; margin-top: 8px; }
                    button:hover { background: #1d4ed8; }
                </style>
            </head>
            <body>
                <div class="card">
                    <h2>Get In Touch</h2>
                    <p>Fill in your details below and our team will respond to you instantly on WhatsApp.</p>
                    <form action="'.route('shareable.capture.submit', ['token' => $token]).'" method="POST">
                        '.csrf_field().'
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" name="name" required placeholder="e.g. John Doe">
                        </div>
                        <div class="form-group">
                            <label>Phone / WhatsApp Number *</label>
                            <input type="tel" name="phone" required placeholder="e.g. +91 9876543210">
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" placeholder="e.g. john@example.com">
                        </div>
                        <div class="form-group">
                            <label>How can we help you?</label>
                            <textarea name="description" rows="3" placeholder="Tell us about your requirements..."></textarea>
                        </div>
                        <button type="submit">Submit Request</button>
                    </form>
                </div>
            </body>
            </html>
        ', 200, ['Content-Type' => 'text/html']);
    }

    /**
     * Submit public lead capture form.
     */
    public function store(Request $request, $token = 'default')
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'description' => 'nullable|string',
        ]);

        $phone = $request->input('phone');
        $email = $request->input('email');
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

        // De-duplicate Person. Stored contact_numbers is JSON holding the *formatted*
        // number, so strip separators before matching (same as LeadCaptureController).
        $person = DB::table('persons')
            ->whereRaw("REPLACE(REPLACE(REPLACE(contact_numbers, ' ', ''), '-', ''), '+', '') LIKE ?", ["%{$cleanPhone}%"])
            ->first();

        $assignedUserId = $this->distributionService->getNextAssignedUserId();

        if (! $person) {
            $person = $this->personRepository->create([
                'entity_type' => 'persons',
                'name' => $request->input('name'),
                'emails' => $email ? [['value' => $email, 'label' => 'work']] : [],
                'contact_numbers' => [['value' => $phone, 'label' => 'mobile']],
                'user_id' => $assignedUserId,
            ]);
        }

        $source = $this->sourceRepository->findOneByField('name', 'QR / Capture Link');
        if (! $source) {
            $source = $this->sourceRepository->create(['name' => 'QR / Capture Link']);
        }

        $pipeline = $this->pipelineRepository->getDefaultPipeline();
        $stage = $pipeline->stages->first();

        $this->leadRepository->create([
            'entity_type' => 'leads',
            'title' => 'Web Form Inquiry - '.$request->input('name'),
            'description' => $request->input('description'),
            'lead_value' => 0,
            'user_id' => $assignedUserId,
            'person_id' => $person->id,
            'lead_source_id' => $source->id,
            'lead_pipeline_id' => $pipeline->id,
            'lead_pipeline_stage_id' => $stage->id,
            'status' => 1,
        ]);

        return response()->make('
            <!DOCTYPE html>
            <html lang="en">
            <head><meta charset="UTF-8"><title>Thank You</title><style>body { font-family: system-ui; text-align: center; padding: 50px; background: #f8fafc; } .card { background: white; padding: 40px; border-radius: 16px; max-width: 400px; margin: auto; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); }</style></head>
            <body><div class="card"><h2>Thank You!</h2><p>Your request has been received. Our team will contact you shortly.</p></div></body>
            </html>
        ', 200, ['Content-Type' => 'text/html']);
    }
}
