<?php

namespace Database\Seeders;

use App\Models\JobOpening;
use Illuminate\Database\Seeder;

class JobOpeningSeeder extends Seeder
{
    public function run(): void
    {
        if (JobOpening::exists()) {
            return;
        }

        $rows = [
            [
                'title' => 'Sales Executive - Laboratory Instruments',
                'slug' => 'sales-executive-laboratory-instruments',
                'location' => 'Hyderabad',
                'employment_type' => 'Full Time',
                'department' => 'Sales',
                'summary' => 'Build relationships with pharma, biotech and academic labs and help them choose the right instruments.',
                'description' => '<p>You will visit customers, understand their lab workflows, prepare quotations and work with our technical team to close orders.</p><ul><li>Handle a territory of lab customers</li><li>Prepare quotations and follow up on enquiries</li><li>Coordinate demos with principals and our application team</li></ul>',
                'questions' => [
                    ['label' => 'Years of experience in B2B / scientific sales', 'type' => 'select', 'options' => 'Fresher, 1-3 years, 3-5 years, 5+ years', 'required' => true],
                    ['label' => 'Which instrument categories have you sold before?', 'type' => 'textarea', 'options' => null, 'required' => false],
                    ['label' => 'Do you have a two-wheeler / four-wheeler for field visits?', 'type' => 'yes_no', 'options' => null, 'required' => true],
                ],
                'sort_order' => 1,
            ],
            [
                'title' => 'Service Engineer - Analytical Instruments',
                'slug' => 'service-engineer-analytical-instruments',
                'location' => 'Hyderabad',
                'employment_type' => 'Full Time',
                'department' => 'Service',
                'summary' => 'Install, calibrate and maintain HPLC, GC and spectroscopy systems at customer sites.',
                'description' => '<p>You will own installations, preventive maintenance and breakdown calls for analytical instruments across our customer base.</p>',
                'questions' => [
                    ['label' => 'Instruments you have serviced (HPLC, GC, UV-Vis, etc.)', 'type' => 'textarea', 'options' => null, 'required' => true],
                    ['label' => 'Are you willing to travel across the region?', 'type' => 'yes_no', 'options' => null, 'required' => true],
                ],
                'sort_order' => 2,
            ],
            [
                'title' => 'Marketing Executive - Digital',
                'slug' => 'marketing-executive-digital',
                'location' => 'Hyderabad',
                'employment_type' => 'Full Time',
                'department' => 'Marketing',
                'summary' => 'Run our social media, email campaigns and website content for the laboratory market.',
                'description' => '<p>Plan and publish content, manage campaigns and report on enquiries generated from digital channels.</p>',
                'questions' => [
                    ['label' => 'Link to your portfolio or past work', 'type' => 'text', 'options' => null, 'required' => false],
                ],
                'sort_order' => 3,
            ],
        ];

        foreach ($rows as $row) {
            JobOpening::create($row + ['is_active' => true]);
        }
    }
}
