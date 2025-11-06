<?php
session_start();
$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);
?>
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
<h1>Добавить рецепт</h1>
<form action="/handlers/create_recipe_handlers.php" method="post">
    <label>Название рецепта:<br>
        <input type="text" name="title">
        <span style="color:red"><?= $errors['title'] ?? '' ?></span>
    </label><br><br>

    <label>Категория:<br>
        <select name="category">
            <option>Закуски</option>
            <option>Основное блюдо</option>
            <option>Десерт</option>
        </select>
    </label><br><br>

    <label>Ингредиенты:<br>
        <textarea name="ingredients" rows="4" cols="50"></textarea>
        <span style="color:red"><?= $errors['ingredients'] ?? '' ?></span>
    </label><br><br>

    <label>Описание:<br>
        <textarea name="description" rows="4" cols="50"></textarea>
        <span style="color:red"><?= $errors['description'] ?? '' ?></span>
    </label><br><br>

    <label>Тэги:<br>
        <select name="tags[]" multiple>
            <option>Легко</option>
            <option>Быстро</option>
            <option>Веган</option>
            <option>Сытно</option>
        </select>
    </label><br><br>

    <label>Шаги приготовления:<br>
        <textarea name="steps" rows="6" cols="50"></textarea>
        <span style="color:red"><?= $errors['steps'] ?? '' ?></span>
    </label><br><br>

    <button type="submit">Отправить</button>
</form>
<br>
<a href="/index.php">На главную</a>
</body>
</html>
