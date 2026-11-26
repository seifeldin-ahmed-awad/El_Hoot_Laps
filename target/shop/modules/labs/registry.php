<?php
declare(strict_types=1);

/** Return the lab metadata displayed by the Kanso Goods shop. */
function shop_lab_registry(PDO $pdo): array
{
    $columns = $pdo->query(
        "SELECT COLUMN_NAME
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE()
           AND TABLE_NAME   = 'labs'
           AND COLUMN_NAME  = 'is_enabled'"
    );
    $hasEnabledColumn = $columns !== false && $columns->fetchColumn() !== false;

    $selectFields = $hasEnabledColumn
        ? 'id, category, title, description, difficulty, points, is_enabled'
        : 'id, category, title, description, difficulty, points';

    $statement = $pdo->query(
        "SELECT {$selectFields} FROM labs ORDER BY id"
    );

    $registry = [];
    foreach ($statement->fetchAll() as $lab) {
        $enabled = $hasEnabledColumn ? (bool) $lab['is_enabled'] : false;

        $registry[] = [
            'id'          => (int) $lab['id'],
            'title'       => (string) $lab['title'],
            'category'    => (string) $lab['category'],
            'description' => (string) $lab['description'],
            'difficulty'  => (string) $lab['difficulty'],
            'points'      => (int) $lab['points'],
            'enabled'     => $enabled,
        ];
    }

    return $registry;
}
