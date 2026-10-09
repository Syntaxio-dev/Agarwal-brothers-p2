<?php

namespace Database\Seeders;

use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        if (Review::exists()) {
            return;
        }

        $rows = [
            ['Dr. Rahul Gupta', 'Research Scientist', 'CSIR-CDRI, Lucknow', 'Working with Agarwal Brothers has been exceptional. The rotary evaporator we sourced through them transformed our solvent recovery workflow, and their technical team stayed available even after installation.'],
            ['Priya Malhotra', 'QC Manager', 'Sun Pharmaceuticals', 'Their responsiveness sets them apart. When our HPLC needed urgent repair before a key audit, an engineer was on-site within 24 hours. That kind of partnership makes a real difference in pharma QC.'],
            ['Prof. Suresh Mehta', 'Head of Department', 'IIT Bombay', 'We have been procuring laboratory equipment from Agarwal Brothers for over a decade. Their technical depth and access to global brands make them our first call for any new lab setup or upgrade.'],
        ];

        foreach ($rows as $i => [$name, $designation, $org, $content]) {
            Review::create([
                'name' => $name,
                'designation' => $designation,
                'organization' => $org,
                'content' => $content,
                'rating' => 5,
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }
    }
}
