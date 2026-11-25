<?php
declare(strict_types=1);

/**
 * Returns the full lab registry for the Kanso Goods shop.
 *
 * The enabled state of each lab is read from the `is_enabled` column in
 * the MySQL labs table (added by migration 006_lab_enabled_and_hash_algo.sql).
 * No lab ID is hardcoded here; enabling or disabling a lab requires only a
 * database change.
 *
 * Falls back gracefully when the is_enabled column does not yet exist on an
 * older installation that has not run migration 006.
 */
function shop_lab_registry(PDO $pdo): array
{
    // Detect whether the is_enabled column exists so the registry degrades
    // cleanly on installations that have not yet applied migration 006.
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
        $labId = (int) $lab['id'];

        // When the column exists, honour the database value.
        // When it does not exist yet, nothing is enabled until the migration runs.
        $enabled = $hasEnabledColumn ? (bool) $lab['is_enabled'] : false;

        $registry[] = [
            'id'          => $labId,
            'title'       => (string) $lab['title'],
            'category'    => (string) $lab['category'],
            'description' => (string) $lab['description'],
            'difficulty'  => (string) $lab['difficulty'],
            'points'      => (int) $lab['points'],
            'enabled'     => $enabled,
            'handler'     => $enabled ? ('lab' . str_pad((string) $labId, 2, '0', STR_PAD_LEFT)) : null,
            'completion'  => 'Submit a configured flag through the BreachLab Scoreboard.',
        ];
    }

    return $registry;
}
