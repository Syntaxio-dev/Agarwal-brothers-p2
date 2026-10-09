<?php

namespace Database\Seeders;

use App\Models\TeamMember;
use Illuminate\Database\Seeder;

class TeamMemberSeeder extends Seeder
{
    public function run(): void
    {
        if (TeamMember::exists()) {
            return;
        }

        // Dummy entries: replace names, photos and quotes from the admin panel.
        $rows = [
            ['Founder Name', 'Chairman', true,
                'When we started this journey, we knew it was not enough to build a company. We had to build trust, values and people who could carry them forward.'],
            ['Managing Director', 'Managing Director', true,
                'My responsibility has been to shape a strong vision into a company that can endure, expand and deliver greater impact for every lab we serve.'],
            ['Director Name', 'Director, Operations', true,
                'Growth only has meaning when it strengthens the foundation, empowers every department and keeps us focused on serving customers better.'],
            ['Head of Sales', 'Sales & Business Development', false, null],
            ['Head of Service', 'Technical Service & Support', false, null],
        ];

        foreach ($rows as $i => [$name, $designation, $leader, $quote]) {
            TeamMember::create([
                'name' => $name,
                'designation' => $designation,
                'is_leader' => $leader,
                'quote' => $quote,
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }
    }
}
