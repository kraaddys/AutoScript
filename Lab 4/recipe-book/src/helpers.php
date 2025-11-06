<?php

/**
 * Загружает рецепты из файла и возвращает их в виде массива.
 * @return array
 */
function loadRecipes(): array {
    $file = __DIR__ . '/../storage/recipes.txt';
    if (!file_exists($file)) return [];
    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return array_map('json_decode', $lines);
}
