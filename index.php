<?php
session_start();
$database = 'global';

try {
    $user = "root";
    $pdo = new PDO("mysql:host=localhost;dbname=$database;charset=utf8", $user);
}
catch (PDOException $e) {
//    die('Подключение не удалось: ' . $e->getMessage());
//    die('Подключение не удалось: ');
    $user = "estukalov";
    $password = "neto1205";
    $pdo = new PDO("mysql:host=localhost;dbname=$database;charset=utf8", $user, $password);
}
$array = [];
$description = [];
$order = ' ORDER BY date_added;';
$sort_array = ['date_added', 'is_done', 'description'];
$add_edit = 'add';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST)) {

    if (isset($_POST['var']) & !empty($_POST['var'])) {

        if (isset($_POST['add_edit']) && $_POST['add_edit'] == 'edit' && isset($_GET['id'])) {
            $sql = "UPDATE tasks SET description = :description WHERE id=:id;";
            $array = ['description'=>htmlspecialchars($_POST['var']), 'id'=>$_GET['id']];
        }
        else {
            $sql = "INSERT INTO tasks (description, is_done, date_added) VALUES (:description, :is_done, :date_added)";
            $array = ['description'=>htmlspecialchars($_POST['var']), 'is_done'=>0, 'date_added'=>date('Y.m.d Hi:s:',time())];
        }

        $statement = $pdo->prepare($sql);
        $statement->execute($array);
    }

    if (isset($_POST['my_sort']) && !empty($_POST['my_sort']) && in_array($_POST['my_sort'], $sort_array)) {
        $_SESSION['order'] = ' ORDER BY ' . ($_POST['my_sort']) . ';';
    }

}

if (isset($_GET['action']) && $_GET['action']=='edit' && !isset($_POST['my_sort']) && !isset($_POST['var'])) {
    $add_edit = 'edit';
}

if ($_SERVER['REQUEST_METHOD'] == 'GET' && !empty($_GET)) {

    if (isset($_GET['action'])) {

        if ($_GET['action'] == 'edit') {
            $sql = "SELECT description FROM tasks WHERE id=:id;";
            $array = ['id'=>$_GET['id']];
        }
        else {

            if ($_GET['action'] == 'delete') {
                $sql = "DELETE FROM tasks WHERE id=:id;";
                $array = ['id'=>$_GET['id']];
            }
            elseif ($_GET['action'] == 'done') {
                $sql = "UPDATE tasks SET is_done = :is_done WHERE id=:id;";
                $array = ['is_done'=>1, 'id'=>$_GET['id']];
            }

        }

        $statement = $pdo->prepare($sql);
        $statement->execute($array);

        if ($_GET['action'] == 'edit' & $add_edit == 'edit') {
            $description = $statement -> fetchall(PDO::FETCH_COLUMN, 0);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST)) {
    header("Location: index.php");
    exit;
}

if (isset($_SESSION['order']) && !empty($_SESSION['order'])) {
    $order = $_SESSION['order'];
}

$sql = "SELECT * FROM tasks" . $order;
$statement = $pdo->prepare($sql);
$statement->execute($array);

while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
    $results[] = $row;
}

unset($_SESSION['order']);

?>
<html lang='ru'>
<head>
    <meta charset='UTF-8'>
    <title>MySQL</title>
    <style>
        td {padding: 5px 20px 5px 20px;border: 1px solid black}
        thead td {text-align: center;background-color: #dbdbdb;font-weight: 700;}
        table {border-collapse: collapse;border-spacing: 0}
        .done {margin-right: 20px;}
    </style>
</head>
<body>
    <h1>Список дел на сегодня</h1>

    <div style="float: left">
        <form method='POST'>
            <input type="hidden" name="add_edit" value="<?=$add_edit?>">
            <input type="text" name="var" placeholder='Описание задачи' value="<?=!empty($description)? $description[0]:''?>">
            <input type='submit' value=<?=$add_edit=='edit' ? 'Сохранить' : 'Добавить'?>>
        </form>
    </div>

    <div style="float: left">
        <form method='POST'>
            <label for="sort">Сортировать по:</label>
            <select name="my_sort">
                <option value="date_added">Дате добавления</option>
                <option value="is_done">Статусу</option>
                <option value="description">Описанию</option>
            </select>
            <input type='submit' value='Отсортировать' name='sort'>
        </form>
    </div>

    <div style="clear: both"></div>
    <table>
        <thead>
            <tr>
                <td>Описание задачи</td>
                <td>Дата обновления</td>
                <td>Статус</td>
                <td></td>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($results)) { foreach ($results as $value) :?>
            <tr>
                <td><?=$value['description']?></td>
                <td><?=$value['date_added']?></td>
                <td><span style=<?=!$value['is_done']?'color:orange;':'color:green;'?>><?=!$value['is_done']?'В процессее':'Выполнено'?></span></td>
                <td><a class="done" href=<?="?id=".$value['id']."&action=edit"?>>Изменить</a><a class="done" href=<?="?id=".$value['id']."&action=done"?>>Выполнить</a><a class="done" href=<?="?id=".$value['id']."&action=delete"?>>Удалить</a></td>
            </tr>
            <?php endforeach; } ?>
        </tbody>
    </table>

</body>
</html>