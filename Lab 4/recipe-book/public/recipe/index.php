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
require_once __DIR__ . '/../../src/helpers.php';

$recipes = array_reverse(loadRecipes());
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 5;
$total = count($recipes);
$offset = ($page - 1) * $perPage;
$recipesPage = array_slice($recipes, $offset, $perPage);

echo "<h1>Все рецепты</h1>";
foreach ($recipesPage as $recipe) {
    echo "<h2>" . htmlspecialchars($recipe->title) . "</h2>";
    echo "<strong>Категория:</strong> " . htmlspecialchars($recipe->category) . "<br>";
    echo "<strong>Ингредиенты:</strong><br>" . nl2br(htmlspecialchars($recipe->ingredients)) . "<br>";
    echo "<strong>Описание:</strong><br>" . nl2br(htmlspecialchars($recipe->description)) . "<br>";
    echo "<strong>Шаги:</strong><br>" . nl2br(htmlspecialchars($recipe->steps)) . "<br>";
    echo "<strong>Теги:</strong> " . implode(", ", $recipe->tags) . "<br>";
    echo "<em>Добавлено: " . $recipe->created_at . "</em><hr>";
}

$totalPages = ceil($total / $perPage);
for ($i = 1; $i <= $totalPages; $i++) {
    echo "<a href='?page=$i'>Страница $i</a> ";
}
echo '<br><a href="/index.php">На главную</a>';