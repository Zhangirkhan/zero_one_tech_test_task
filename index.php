<?php
header("Access-Control-Allow-Origin: *");
?>
<!DOCTYPE html>
<html lang="RU">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Работа с картой YANDEX MAP API 2.1</title>
    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
    <!-- Core theme CSS (includes Bootstrap)-->
    <link href="css/styles.css" rel="stylesheet" />
    <link href="css/custom.css" rel="stylesheet" />
    <script src="https://yandex.st/jquery/2.2.3/jquery.min.js" type="text/javascript"></script>
    <script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU&amp;apikey=39583abb-da7f-4185-912d-c2d61b8eb612" type="text/javascript"></script>
    <script src="object_manager.js" type="text/javascript"></script>
</head>

<body>
    <!-- Responsive navbar-->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#!">Работа с картой</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item"><a class="nav-link" href="mailto:zendarol@mail.ru">Написать автору</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Page header with logo and tagline-->
    <header class="py-5 bg-light border-bottom mb-4">
        <div class="container">
            <div class="text-center my-5">
                <h1 class="fw-bolder">Работа с картой</h1>
                <p class="lead mb-0">Работа с яндекс картой</p>
            </div>
        </div>
    </header>
    <!-- Page content-->
    <div class="container">
        <div class="row">
            <!-- Blog entries-->
            <div class="col-lg-8">
                <!-- Featured blog post-->
                <div class="card mb-4">
                    <!-- ТУТ РАЗМЕЩАЕТЬСЯ КАРТА -->
                    <div id="map"></div>
                </div>
            </div>
            <!-- Side widgets-->
            <div class="col-lg-4">
                <!-- Search widget-->
                <div class="card mb-4">
                    <div class="card-header">Введите адрес</div>
                    <div class="card-body form-inline">
                        <div class="form-group">
                            <div class="input-group mb-3" id="header">
                                <input type="text" id="suggest" class=" form-control" placeholder="Введите адрес" aria-label="Введите адрес" aria-describedby="button-addon2">
                                <button class="btn btn-outline-secondary" type="submit" id="button">Проверить</button>
                            </div>

                            <div id="footer">

                                <form method="post" id="create-address-form">
                                    <div class="mb-3">
                                        <label for="address" class="form-label">Адрес</label>
                                        <input type="text" class="form-control" name="address" id="address" value="" aria-describedby="emailHelp">
                                    </div>
                                    <div class="mb-3">
                                        <label for="latitude" class="form-label">Координаты</label>
                                        <input type="text" class="form-control" name="latitude" id="latitude" value="">
                                    </div>
                                    <div class="mb-3">
                                        <label for="longitude" class="form-label">Координаты</label>
                                        <input type="text" class="form-control" name="longitude" id="longitude" value="">
                                    </div>
                                    <button type="button" id="save_address" class="btn btn-primary">Сохранить</button>
                                    <div id="messageHeader"></div>
                                    <div id="message"></div>
                                </form>
                            </div>
                            <div id="notice_footer">
                                <p id="notice">Адрес не найден</p>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card mb-4">
                        <div class="card-header">Адреса из базы</div>
                        <div class="card-body">
                            <?
                            // подключение к базе данных будет здесь
                            include_once './api/config/database.php';
                            include_once './api/objects/coord.php';

                            // получаем соединение с базой данных
                            $database = new Database();
                            $db = $database->getConnection();

                            // инициализируем объект
                            $coord = new Coord($db);
                            // запрашиваем товары
                            $stmt = $coord->read();
                            $num = $stmt->rowCount();

                            // проверка, найдено ли больше 0 записей
                            if ($num > 0) {

                                // получаем содержимое нашей таблицы
                                // fetch() быстрее, чем fetchAll()
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                                    // извлекаем строку
                                    extract($row);

                                    $coord_item = array(
                                        // "id" => $id,
                                        "coords" => array($latitude, $longitude),
                                        "text" => $address,
                                    );

                                    // echo $address. " ". $latitude.",".$longitude;

                                    echo '<div class="input-group mb-3">
                            <input type="text" disabled class="form-control" value="' . $address . '" >
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Действие</button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" onclick="deleteAddress(' . $id . ');">Удалить</a></li>
                            </ul>
                        </div>';
                                }
                            } else {

                                echo "Вы еще не добавили адрес";
                            }

                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer-->
    <footer class="py-5 bg-dark">
        <div class="container">
            <p class="m-0 text-center text-white">Copyright &copy; Нургалиев Жангирхан 2021</p>
        </div>
    </footer>
    <!-- Bootstrap core JS-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Core theme JS-->
    <script src="js/scripts.js"></script>
</body>

</html>
