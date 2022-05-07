<?php

namespace Database\Factories;

use App\Models\CarMade;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ProductCategory;

class CarMadeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CarMade::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'categoryid_id' => function() { 
                return ProductCategory::all()->random()->id;
                },
            'car_made'      => $this->faker->word,
        ];
    }
}
