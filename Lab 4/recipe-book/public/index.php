<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Каталог рецептов</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
<nav>
    <a href="/index.php">Главная</a>
    <a href="/recipe/index.php">Все рецепты</a>
    <a href="/recipe/create.php">Добавить рецепт</a>
</nav>
<?php
require_once __DIR__ . '/../src/helpers.php';

$recipes = loadRecipes();
$latestRecipes = array_slice($recipes, -2);

echo "<h1>Последние рецепты</h1>";
foreach ($latestRecipes as $recipe) {
    echo "<h2>" . htmlspecialchars($recipe->title) . "</h2>";
    echo "<p>" . nl2br(htmlspecialchars($recipe->description)) . "</p><hr>";
}
echo '<a href="/recipe/create.php">Добавить рецепт</a> | <a href="/recipe/index.php">Все рецепты</a>';