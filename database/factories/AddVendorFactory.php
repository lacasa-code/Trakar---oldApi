<?php

namespace Database\Factories;

use App\Models\AddVendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use Str;

class AddVendorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AddVendor::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'serial'       => Str::random(5),
            'vendor_name'  => $this->faker->name,
            'email'        => $this->faker->email,
            'type'         => $this->faker->numberBetween(1, 3),
            'userid_id'    => function() { return User::all()->random()->id; },
        ];
    }
}
