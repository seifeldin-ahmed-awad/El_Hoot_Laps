<?php
declare(strict_types=1);

function config_environment(string $name, string|false|null $fallback = false): string|false|null
{
    $value = getenv($name);

    return $value === false ? $fallback : $value;
}

function config_database(): array
{
    return [
        'host' => config_environment('BREACHLAB_DB_HOST'),
        'port' => config_environment('BREACHLAB_DB_PORT'),
        'name' => config_environment('BREACHLAB_DB_NAME'),
        'username' => config_environment('BREACHLAB_DB_USER'),
        'password' => config_environment('BREACHLAB_DB_PASSWORD'),
    ];
}

function config_lab_postgres(int $labId): array
{
    $suffix = str_pad((string) $labId, 2, '0', STR_PAD_LEFT);

    return [
        'host' => config_environment("LAB{$suffix}_PG_HOST", '127.0.0.1'),
        'port' => config_environment("LAB{$suffix}_PG_PORT", '5432'),
        'database' => config_environment("LAB{$suffix}_PG_DATABASE", "lab{$suffix}_sqli"),
        'username' => config_environment("LAB{$suffix}_PG_USER", "lab{$suffix}_user"),
        'password' => config_environment("LAB{$suffix}_PG_PASSWORD", "lab{$suffix}_localdev"),
    ];
}

function config_lab09_stock_api(): string
{
    return (string) config_environment(
        'BREACHLAB_LAB09_STOCK_API',
        'http://stock.kansogoods.test/stock'
    );
}
