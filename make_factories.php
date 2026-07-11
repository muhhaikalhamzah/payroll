<?php

$factories = [
    'Department' => <<<PHP
            'name' => fake()->unique()->jobTitle() . ' Department',
            'description' => fake()->sentence(),
            'parent_department_id' => null,
PHP,
    'Position' => <<<PHP
            'name' => fake()->jobTitle(),
            'department_id' => \App\Models\Department::factory(),
PHP,
    'SalaryComponent' => <<<PHP
            'name' => fake()->word() . ' Allowance',
            'type' => fake()->randomElement(['allowance', 'deduction']),
            'is_fixed' => fake()->boolean(),
            'is_taxable' => fake()->boolean(),
PHP,
    'SalaryStructure' => <<<PHP
            'position_id' => \App\Models\Position::factory(),
            'base_salary' => fake()->randomFloat(2, 3000000, 15000000),
PHP
];

foreach ($factories as $model => $def) {
    $file = __DIR__ . "/database/factories/{$model}Factory.php";
    $content = file_get_contents($file);
    $content = preg_replace('/return \[\s*\/\/\s*\];/', "return [\n$def\n        ];", $content);
    file_put_contents($file, $content);
}

echo "Factories updated successfully.\n";
