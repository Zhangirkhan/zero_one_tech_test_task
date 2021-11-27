<?php
// необходимые HTTP-заголовки
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// подключение к базе данных будет здесь
include_once './config/database.php';
include_once './objects/coord.php';


// получаем соединение с базой данных
$database = new Database();
$db = $database->getConnection();

// инициализируем объект
$coord = new Coord($db);

// запрашиваем товары
$stmt = $coord->read();
$num = $stmt->rowCount();

// проверка, найдено ли больше 0 записей
if ($num>0) {

    // массив товаров
    $coords_arr=array();
    $coords_arr["records"]=array();

    // получаем содержимое нашей таблицы
    // fetch() быстрее, чем fetchAll()
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){

        // извлекаем строку
        extract($row);
        //Так мы преобразовываем координаты для удобного превращения в объекта для карты
        $coord_item=array(
            "coords" => array($latitude, $longitude),
            "text" => $address,
        );

        array_push($coords_arr["records"], $coord_item);
    }

    // устанавливаем код ответа - 200 OK
    http_response_code(200);

    // выводим данные о товаре в формате JSON
    echo json_encode($coords_arr["records"], JSON_UNESCAPED_UNICODE);
}

else {

    // установим код ответа - 404 Не найдено
    http_response_code(404);

    // сообщаем пользователю, что товары не найдены
    echo json_encode(array("message" => "Товары не найдены."), JSON_UNESCAPED_UNICODE);
}
