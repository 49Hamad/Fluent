<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\FormEvaluation;
use Faker\Factory as Faker;
class FormEvaluationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        for ($i = 0; $i < 3000; $i++) {
            // Generate a random start project date
            $startProjectDate = $faker->dateTimeBetween('-2 years', 'now');

            // Generate an evaluation date that is after the start project date
            $evaluationDate = $faker->dateTimeBetween($startProjectDate->format('Y-m-d'), '+1 year');

            FormEvaluation::create([
                'client_name' => $faker->name(),
                'company_name' => $faker->company(),
                'email' => $faker->unique()->safeEmail(),
                'feedback' => $faker->sentence(),
                'start_project_date' => $startProjectDate->format('Y-m-d'),
                'evaluation_date' => $evaluationDate->format('Y-m-d'),
                'is_active' => false,
            ]);
        }

    }
}
