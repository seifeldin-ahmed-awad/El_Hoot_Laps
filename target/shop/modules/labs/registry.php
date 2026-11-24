<?php
declare(strict_types=1);

function shop_lab_registry(PDO $pdo): array
{
    $statement = $pdo->query(
        'SELECT id, category, title, description, difficulty, points
         FROM labs ORDER BY id'
    );
    $registry = [];
    foreach ($statement->fetchAll() as $lab) {
        $registry[] = [
            'id' => (int) $lab['id'],
            'title' => (string) $lab['title'],
            'category' => (string) $lab['category'],
            'description' => (string) $lab['description'],
            'difficulty' => (string) $lab['difficulty'],
            'points' => (int) $lab['points'],
            'enabled' => false,
            'handler' => null,
            'completion' => 'Submit a configured flag through the BreachLab Scoreboard.',
        ];
    }

    return $registry;
}
