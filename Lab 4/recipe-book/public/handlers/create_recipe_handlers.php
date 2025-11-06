<?php
session_start();
require_once __DIR__ . '/../../src/helpers.php';

$title = trim($_POST['title'] ?? '');
$category = $_POST['category'] ?? '';
$ingredients = trim($_POST['ingredients'] ?? '');
$description = trim($_POST['description'] ?? '');
$tags = $_POST['tags'] ?? [];
$steps = trim($_POST['steps'] ?? '');

$errors = [];

if ($title === '') $errors['title'] = 'Введите название.';
if ($ingredients === '') $errors['ingredients'] = 'Введите ингредиенты.';
if ($description === '') $errors['description'] = 'Введите описание.';
if ($steps === '') $errors['steps'] = 'Введите шаги.';

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header('Location: /recipe/create.php');
    exit;
}

$recipe = [
    'title' => htmlspecialchars($title),
    'category' => $category,
    'ingredients' => htmlspecialchars($ingredients),
    'description' => htmlspecialchars($description),
    'tags' => $tags,
    'steps' => htmlspecialchars($steps),
    'created_at' => date('Y-m-d H:i:s')
];

file_put_contents(__DIR__ . '/../../storage/recipes.txt', json_encode($recipe) . PHP_EOL, FILE_APPEND);
header('Location: /index.php');
exit;
