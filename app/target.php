<?php
declare(strict_types=1);

function configured_target_lab_id(): ?int
{
    static $resolved = false;
    static $labId = null;

    if ($resolved) {
        return $labId;
    }

    $resolved = true;
    $statement = database()->query(
        'SELECT id, challenge_route, expected_flag_hash
         FROM labs
         WHERE challenge_route IS NOT NULL
           AND challenge_route <> \'\'
           AND expected_flag_hash IS NOT NULL
         ORDER BY id'
    );

    foreach ($statement as $lab) {
        if (!is_string($lab['expected_flag_hash']) ||
            preg_match('/\A[a-f0-9]{64}\z/i', $lab['expected_flag_hash']) !== 1 ||
            safe_challenge_url($lab['challenge_route']) === null) {
            continue;
        }

        $labId = (int) $lab['id'];
        break;
    }

    return $labId;
}
