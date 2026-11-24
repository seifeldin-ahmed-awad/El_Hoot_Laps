<?php
declare(strict_types=1);

function e(string|int|float|null $value): string
{
  return escape_html((string) $value);
}

function load_leaderboard(PDO $pdo): array
{
  $operators = $pdo->query(
    'SELECT o.id AS operator_id, o.username,
            COUNT(c.id) AS solved,
            COALESCE(SUM(c.points_awarded), 0) AS score,
            COALESCE(MAX(c.completed_at), o.created_at) AS last_solve
     FROM operators AS o
     LEFT JOIN lab_completions AS c ON c.operator_id = o.id
     GROUP BY o.id, o.username, o.created_at
     ORDER BY score DESC, last_solve ASC, o.id ASC'
  )->fetchAll();

  foreach ($operators as $index => $operator) {
    $operators[$index] = [
      'id' => (int) $operator['operator_id'],
      'username' => (string) $operator['username'],
      'solved' => (int) $operator['solved'],
      'score' => (int) $operator['score'],
      'rank' => $index + 1,
    ];
  }

  return $operators;
}

function find_standing(array $leaderboard, ?array $user): ?array
{
  if ($user === null) {
    return null;
  }

  $operatorId = (int) ($user['id'] ?? 0);
  foreach ($leaderboard as $operator) {
    if ($operator['id'] === $operatorId) {
      return $operator;
    }
  }

  return null;
}
