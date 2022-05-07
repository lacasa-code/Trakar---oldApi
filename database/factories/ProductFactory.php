<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;
use App\Models\CarMade;
use App\Models\CarModel;
use App\Models\PartCategory;
use App\Models\AddVendor;
use App\Models\CarYear;
use App\Models\Store;

class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'car_made_id'       => function() { return CarMade::all()->random()->id; },
            'car_model_id'      => function() { return CarModel::all()->random()->id; },
            'year_id'           => function() { return CarYear::all()->random()->id; },
            'part_category_id'  => function() { return PartCategory::all()->random()->id; },
            'vendor_id'         => function() { return AddVendor::all()->random()->id; },
            'name'              => $this->faker->word,
            'description'       => $this->faker->sentence,
            'discount'          => $this->faker->numberBetween(1, 8),
            'price'             => $this->faker->numberBetween(1, 1000),
            'store_id'          => function() { return Store::all()->random()->id; },
            'quantity'          => $this->faker->numberBetween(1, 500),
            'serial_number'     => Str::random(10),
        ];
    }
}
