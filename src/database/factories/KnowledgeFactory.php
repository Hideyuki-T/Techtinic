<?php

namespace Database\Factories;

use App\Models\Knowledge;
use Illuminate\Database\Eloquent\Factories\Factory;

class KnowledgeFactory extends Factory
{
    protected $model = Knowledge::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'content' => $this->faker->paragraph,
            // その他必要なフィールドを追加
        ];
    }
}
