<?php
/**
* Автор: Репник Кристиан
*
* Дата реализации: 09.11.2022 12:57
*
* Дата изменения: 10.11.2022 8:31
*
* Утилита для работы с базой данных пользователи
*/
/**
Класс пользователь
Класс для работы с БД пользователь. Содержит в себе методы
удаления, сохранения пользователя. 2 статичных метода преобразования
даты в возраст и логического значения пола в строковое выражение.
При создании объекта ищет существующую запись по id пользователя
или создает нового с заданной информацией.
 */
class User
{
    private mysqli $link;
    private int $id;
    private ?string $name;
    private ?string $surname;
    private ?string $birthday;
    private ?string $city;
    private ?bool $sex;

    private function connect($host, $login, $password, $database, $port = 3306)
    {
        $this->link = mysqli_connect($host, $login, $password, $database, $port);
        if (!$this->link) {
            echo "Ошибка: Невозможно установить соединение с MySQL." . PHP_EOL . " <br>";
            echo "Код ошибки errno: " . mysqli_connect_errno() . PHP_EOL . " <br>";
            exit;
        }

        mysqli_set_charset($this->link, "utf8");
    }

    /*
    Конструктор класса.
    Выполняет подключение к базе данных, смотрим количество
    заданных данных, если это id, то ищет пользователя в бд,
    иначе - проверяет данные и создает нового с заданными данными. 
    */
    function __construct(...$user)
    {
        $this->connect("localhost", "root", "", "users");
        if (count($user) === 1) {
            [$this->id] = $user;
            $query = "SELECT * FROM `user` WHERE id = {$this->id}";
            if ($result = mysqli_query($this->link, $query)) {
                if ($record = mysqli_fetch_assoc($result)) {
                    $this->id = $record["id"];
                    $this->name = $record["name"];
                    $this->surname = $record["surname"];
                    $this->birthday = $record["dateof"];
                    $this->city = $record["city"];
                    $this->sex = $record["sex"];
                } else {
                    echo 'No such user <br>';
                    return;
                }
            }
        } elseif (count($user) === 5) {
            [$name, $surname, $birthday, $sex, $city] = $user;
            $format = 'Y-m-d';
            $check = DateTime::createFromFormat($format, $birthday);
            if (!($check && $check->format($format) == $birthday)) {
                echo "wrong date <br>";
                return;
            }
            
            if(empty($name) || empty($surname) || empty($birthday) || empty($city)){
                echo "empty field <br>";
                return;
            }

            $this->name = $name;
            $this->surname = $surname;
            $this->birthday = $birthday;
            $this->sex = $sex;
            $this->city = $city;
            $this->save();
        } else {
            echo "error <br>";
        }
    }

    public function save()
    {
        $query = "INSERT INTO `user` (`name`, `surname`, `dateof`, `sex`, `city`) "
               . "VALUES ('{$this->name}','{$this->surname}','{$this->birthday}','{$this->sex}','{$this->city}')";
        mysqli_query($this->link, $query);
    }

    public function delete()
    {
        $query = "DELETE FROM user WHERE id = {$this->id}";
        if (!mysqli_query($this->link, $query) === TRUE) {
            echo "Error deleting record: " . $this->link->error . " <br>";
        }
    }

    public static function getAge(User $user): int
    {
        $dateOfBirth = $user->birthday;
        $today = date("Y-m-d");
        $diff = date_diff(date_create($today), date_create($dateOfBirth));
        return intval($diff->format('%y'));
    }

    public static function getSex(User $user): string
    {
        return $user->sex == 0 ? 'муж' : 'жен';
    }

    function format($age = true, $sex = true)
    {
        return (object) array(
            'name' => $this->name,
            'surname' => $this->surname,
            ($age ? 'age' : 'birthday') => ($age ? User::getAge($this) : $this->birthday),
            'city' => $this->city,
            'sex' => ($sex ? User::getSex($this) : $this->sex)
        );
    }

    public function __toString()
    {
        return "{$this->name} {$this->surname}";
    }

    function __destruct()
    {
        if (isset($this->link)) {
            mysqli_close($this->link);
        }
    }
}
