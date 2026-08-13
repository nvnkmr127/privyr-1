<?php

namespace Webkul\Installer\Database\Seeders\Lead;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IndustryTemplateSeeder extends Seeder
{
    /**
     * Seed industry pipeline templates.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        $templates = [
            [
                'name' => 'Interior Design Pipeline',
                'ro_code' => 'interior_design',
                'stages' => [
                    ['code' => 'new_lead', 'name' => 'New Lead', 'probability' => 100, 'sort_order' => 1],
                    ['code' => 'site_visit', 'name' => 'Site Visit Scheduled', 'probability' => 80, 'sort_order' => 2],
                    ['code' => 'proposal_sent', 'name' => 'Design Proposal Sent', 'probability' => 60, 'sort_order' => 3],
                    ['code' => 'advance_received', 'name' => 'Advance Received', 'probability' => 90, 'sort_order' => 4],
                    ['code' => 'in_progress', 'name' => 'Project In Progress', 'probability' => 95, 'sort_order' => 5],
                    ['code' => 'completed', 'name' => 'Project Completed (Won)', 'probability' => 100, 'sort_order' => 6],
                    ['code' => 'lost', 'name' => 'Lost', 'probability' => 0, 'sort_order' => 7],
                ],
            ],
            [
                'name' => 'HVAC & Services Pipeline',
                'ro_code' => 'hvac_services',
                'stages' => [
                    ['code' => 'inquiry', 'name' => 'Inquiry Received', 'probability' => 100, 'sort_order' => 1],
                    ['code' => 'inspection', 'name' => 'Inspection Booked', 'probability' => 80, 'sort_order' => 2],
                    ['code' => 'quotation', 'name' => 'Quotation Sent', 'probability' => 60, 'sort_order' => 3],
                    ['code' => 'job_confirmed', 'name' => 'Job Confirmed', 'probability' => 90, 'sort_order' => 4],
                    ['code' => 'service_completed', 'name' => 'Service Completed (Won)', 'probability' => 100, 'sort_order' => 5],
                    ['code' => 'lost', 'name' => 'Lost', 'probability' => 0, 'sort_order' => 6],
                ],
            ],
            [
                'name' => 'Real Estate Pipeline',
                'ro_code' => 'real_estate',
                'stages' => [
                    ['code' => 'new_inquiry', 'name' => 'New Inquiry', 'probability' => 100, 'sort_order' => 1],
                    ['code' => 'site_visit', 'name' => 'Site Visit Done', 'probability' => 70, 'sort_order' => 2],
                    ['code' => 'booking_token', 'name' => 'Booking Token Received', 'probability' => 85, 'sort_order' => 3],
                    ['code' => 'documentation', 'name' => 'Documentation / Loan', 'probability' => 90, 'sort_order' => 4],
                    ['code' => 'registered', 'name' => 'Registered / Handover (Won)', 'probability' => 100, 'sort_order' => 5],
                    ['code' => 'lost', 'name' => 'Lost', 'probability' => 0, 'sort_order' => 6],
                ],
            ],
            [
                'name' => 'Education & Coaching Pipeline',
                'ro_code' => 'education_coaching',
                'stages' => [
                    ['code' => 'lead_captured', 'name' => 'Lead Captured', 'probability' => 100, 'sort_order' => 1],
                    ['code' => 'counseling_call', 'name' => 'Counseling Call Completed', 'probability' => 70, 'sort_order' => 2],
                    ['code' => 'demo_class', 'name' => 'Demo Class Attended', 'probability' => 80, 'sort_order' => 3],
                    ['code' => 'fee_enrolled', 'name' => 'Fee Paid / Enrolled (Won)', 'probability' => 100, 'sort_order' => 4],
                    ['code' => 'dropped', 'name' => 'Dropped', 'probability' => 0, 'sort_order' => 5],
                ],
            ],
            [
                'name' => 'Jewellery Pipeline',
                'ro_code' => 'jewellery',
                'stages' => [
                    ['code' => 'inquiry', 'name' => 'Inquiry', 'probability' => 100, 'sort_order' => 1],
                    ['code' => 'store_visit', 'name' => 'Store Visit Scheduled', 'probability' => 75, 'sort_order' => 2],
                    ['code' => 'design_approval', 'name' => 'Custom Design Approved', 'probability' => 85, 'sort_order' => 3],
                    ['code' => 'advance_paid', 'name' => 'Advance Paid', 'probability' => 95, 'sort_order' => 4],
                    ['code' => 'delivered', 'name' => 'Delivered (Won)', 'probability' => 100, 'sort_order' => 5],
                    ['code' => 'lost', 'name' => 'Lost', 'probability' => 0, 'sort_order' => 6],
                ],
            ],
        ];

        foreach ($templates as $template) {
            $existing = DB::table('lead_pipelines')->where('name', $template['name'])->first();

            if (! $existing) {
                $pipelineId = DB::table('lead_pipelines')->insertGetId([
                    'name' => $template['name'],
                    'is_default' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                foreach ($template['stages'] as $stage) {
                    DB::table('lead_pipeline_stages')->insert([
                        'code' => $stage['code'],
                        'name' => $stage['name'],
                        'probability' => $stage['probability'],
                        'sort_order' => $stage['sort_order'],
                        'lead_pipeline_id' => $pipelineId,
                    ]);
                }
            }
        }
    }
}
