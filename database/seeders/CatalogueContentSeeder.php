<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Fills EMPTY category/product page fields with generic placeholder copy so the
 * new page layouts can be reviewed. Replace everything from the admin panel.
 */
class CatalogueContentSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Category::with('brand')->get() as $category) {
            $name = $category->name;
            $brand = $category->brand?->name ?? 'our principal';

            $category->fill(array_filter([
                'heading' => $category->heading ?: "Best {$name} in India",
                'content' => $category->content ?: "<h2>Where {$name} are used</h2><p>{$brand} {$name} are trusted across research, quality control and production laboratories. Typical users include academic institutions, pharmaceutical and biotech companies, chemical and food laboratories and government testing facilities.</p><h3>What to look for</h3><ul><li>Accuracy and repeatability for your application</li><li>Ease of use and operator safety</li><li>Compliance and documentation support</li><li>Local installation, training and service</li></ul><p>Agarwal Brothers supports every installation with demonstrations, commissioning and after-sales service.</p>",
                'faqs' => $category->faqs ?: [
                    ['question' => "How do I choose the right {$name} model?", 'answer' => 'Share your sample type, throughput and compliance needs with our team and we will recommend a suitable model.'],
                    ['question' => 'Do you provide installation and training?', 'answer' => 'Yes. Installation, user training and application support are included with every instrument we supply.'],
                    ['question' => 'Can I request a demonstration?', 'answer' => 'Yes. Contact us and we will arrange a demonstration at your lab or ours.'],
                ],
            ], fn ($v) => $v !== null))->save();
        }

        foreach (Product::with('category.brand')->get() as $i => $product) {
            $cat = $product->category?->name ?? 'instrument';
            $brand = $product->category?->brand?->name ?? 'the manufacturer';

            $product->fill([
                'model_group' => $product->model_group ?: ($i % 2 === 0 ? 'Standard Models' : 'Advanced Models'),
                'heading' => $product->heading ?: "{$product->name}, Distributor & Service Provider in India",
                'overview' => $product->overview ?: "<p>The {$product->name} from {$brand} is designed for dependable everyday performance in research and industrial laboratories. It combines proven engineering with intuitive operation so your team can work with confidence.</p><p>Supplied, installed and supported in India by Agarwal Brothers.</p>",
                'features' => $product->features ?: [
                    ['title' => 'Intuitive operation', 'text' => 'Clear controls and a simple workflow shorten the learning curve for new users.'],
                    ['title' => 'Robust build quality', 'text' => 'Durable materials designed for demanding daily use.'],
                    ['title' => 'Precision and repeatability', 'text' => 'Consistent results run after run for reliable data.'],
                    ['title' => 'Built-in safety', 'text' => 'Protective features help keep operators and samples safe.'],
                ],
                'advantages' => $product->advantages ?: [
                    ['title' => 'Local service and support', 'text' => 'Installation, calibration and AMC from our trained engineers.'],
                    ['title' => 'Lower cost of ownership', 'text' => 'Long service life and easy maintenance reduce running costs.'],
                    ['title' => 'Flexible configurations', 'text' => "Options and accessories to match your {$cat} workflow."],
                    ['title' => 'Application guidance', 'text' => 'Our specialists help you select and set up the right configuration.'],
                ],
                'faqs' => $product->faqs ?: [
                    ['question' => "What is the {$product->name} used for?", 'answer' => "It is used for {$cat} work in research, quality control and production laboratories."],
                    ['question' => 'How can I request a quotation?', 'answer' => 'Use the Enquiry button on this page and our team will respond with a quotation.'],
                    ['question' => 'Is installation and training included?', 'answer' => 'Yes. Agarwal Brothers provides installation, training and after-sales support.'],
                ],
            ])->save();
        }
    }
}
