<?php

namespace Database\Factories;

use App\Models\CarYear;
use Illuminate\Database\Eloquent\Factories\Factory;

class CarYearFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CarYear::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'year' => date('Y'),
        ];
    }
}
