<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\ClientCategory;
use Illuminate\Console\Command;

class LoadClients extends Command
{
    protected $signature = 'clients:load';

    protected $description = "Add the owner's client list (database/data/clients.php) with category, city and short note. Only adds missing clients (by name), never changes or deletes existing ones.";

    public function handle(): int
    {
        $data = require database_path('data/clients.php');
        $added = 0;
        $order = (int) Client::max('sort_order');

        foreach ($data as $name => [$categoryName, $city, $note]) {
            if (Client::where('name', $name)->exists()) {
                continue;
            }

            $categoryId = null;
            if ($categoryName !== '') {
                $categoryId = ClientCategory::where('name', $categoryName)->value('id');
                if (! $categoryId) {
                    $this->warn("Category \"{$categoryName}\" not found for {$name}; added without a category.");
                }
            }

            Client::create([
                'name' => $name,
                'client_category_id' => $categoryId,
                'city' => $city !== '' ? $city : null,
                'testimonial' => $note !== '' ? $note : null,
                'sort_order' => ++$order,
                'is_active' => true,
                'is_featured' => false,
            ]);
            $added++;
        }

        $this->info("Added {$added} client(s). Clients in total: " . Client::count());

        return self::SUCCESS;
    }
}
