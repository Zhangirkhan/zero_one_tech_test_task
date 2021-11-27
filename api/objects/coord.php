<?php
class Coord
{

    // подключение к базе данных и таблице 'coords'
    private $conn;
    private $table_name = "coords";

    // свойства объекта
    public $id;
    public $address;
    public $latitude;
    public $longitude;

    // конструктор для соединения с базой данных
    public function __construct($db)
    {
        $this->conn = $db;
    }

    // метод read() - получение товаров
    function read()
    {

        // выбираем все записи
        $query = "SELECT
                *
            FROM
                " . $this->table_name . " ORDER BY
                address DESC";

        // подготовка запроса
        $stmt = $this->conn->prepare($query);

        // выполняем запрос
        $stmt->execute();

        return $stmt;
    }

    // метод create - создание товаров
    function create()
    {
        // запрос для вставки (создания) записей
        $query = "INSERT INTO
                " . $this->table_name . "
            SET
                address=:address, latitude=:latitude, longitude=:longitude";

        // подготовка запроса
        $stmt = $this->conn->prepare($query);

        // очистка
        $this->address = htmlspecialchars(strip_tags($this->address));
        $this->latitude = htmlspecialchars(strip_tags($this->latitude));
        $this->longitude = htmlspecialchars(strip_tags($this->longitude));


        // привязка значений
        $stmt->bindParam(":address", $this->address);
        $stmt->bindParam(":latitude", $this->latitude);
        $stmt->bindParam(":longitude", $this->longitude);

        // выполняем запрос
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
    // метод delete - удаление товара
    function delete()
    {

        // запрос для удаления записи (товара)
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";

        // подготовка запроса
        $stmt = $this->conn->prepare($query);

        // очистка
        $this->id = htmlspecialchars(strip_tags($this->id));

        // привязываем id записи для удаления
        $stmt->bindParam(1, $this->id);

        // выполняем запрос
        if ($stmt->execute()) {
            return true;
        }

        return false;
    }
}
