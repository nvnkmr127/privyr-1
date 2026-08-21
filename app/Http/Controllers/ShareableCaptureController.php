<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Webkul\Lead\Contracts\LeadIngestionService;
use Webkul\Lead\DataTransferObjects\LeadIngestionPayload;

class ShareableCaptureController extends Controller
{
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

        $payload = LeadIngestionPayload::fromArray([
            'origin' => 'shareable_link',
            'sourceName' => 'QR / Capture Link',
            'duplicateAction' => 'reject',
            'leadData' => [
                'title' => 'Web Form Inquiry - '.$request->input('name'),
                'description' => $request->input('description'),
                'lead_value' => 0,
                'person_name' => $request->input('name'),
                'emails' => $request->input('email') ? [['value' => $request->input('email'), 'label' => 'work']] : [],
                'contact_numbers' => $request->input('phone') ? [['value' => $request->input('phone'), 'label' => 'mobile']] : [],
            ],
            'metadata' => ['ip' => request()->ip()],
        ]);

        app(LeadIngestionService::class)->ingest($payload);

        return response()->make('
            <!DOCTYPE html>
            <html lang="en">
            <head><meta charset="UTF-8"><title>Thank You</title><style>body { font-family: system-ui; text-align: center; padding: 50px; background: #f8fafc; } .card { background: white; padding: 40px; border-radius: 16px; max-width: 400px; margin: auto; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); }</style></head>
            <body><div class="card"><h2>Thank You!</h2><p>Your request has been received. Our team will contact you shortly.</p></div></body>
            </html>
        ', 200, ['Content-Type' => 'text/html']);
    }
}
