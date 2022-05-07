<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AddVendor;
use Str;

class StoreFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Store::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name'                => Str::random(5),
            'address'             => $this->faker->sentence,
            'lat'                 => '40.11111',
            'long'                => '40.11111',
            'vendor_id'           => function() { return AddVendor::all()->random()->id; }, 
            'moderator_name'      => $this->faker->name,
            'moderator_phone'     => '96611111111',
            'moderator_alt_phone' => '96611111111',
        ];
    }
}
