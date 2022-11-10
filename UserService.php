<?php
/**
* Автор: Репник Кристиан
*
* Дата реализации: 09.11.2022 22:03
*
* Дата изменения: 10.11.2022 8:31
*
* Утилита для работы с базой данных пользователи
*/
if ((include 'User.php') == TRUE) {
    if (!class_exists('User')) {
        exit("<p>Compilation error</p>");
	}
} else{
    exit("<p>Compilation error</p>");
}

/**
Класс пользователь
Класс для работы с БД пользователь. Содержит в себе методы
удаления, сохранения пользователя. 2 статичных метода преобразования
даты в возраст и логического значения пола в строковое выражение.
При создании объекта ищет существующую запись по id пользователя
или создает нового с заданной информацией.
 */
class UserService{
    private $idList = array();
    function __construct($values)
    {
        foreach ($values as $id) {
			array_push($this->idList,$id);
		}
    }

    public function getUsers(){
        $usersList = array();
        foreach($this->idList as $id){
            array_push($usersList,new User($id));
        }

        return $usersList;
    }

    public function deleteUsers(){
        foreach($this->getUsers() as $user){
            $user->delete();
        }

        unset($this->idList);
        $this->idList = array();
    }
}