<?php

/*
// Отладочный заголовок - для индивидуального запуска данного файла. Хотя вообще-то можно запускать индивидуально и без этого...
<!doctype html>
<html>
<head>
<title>Окно регистрации - PHPшный движок</title>
<meta http-equiv="Content-Language" content="ru">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
*/


class DataBaseRequest {    // Весь общий функционал сосредоточен в базовом классе

    protected $admlogin = '';     // Логин для подключения к базе
    protected $admpwd = '';       // Пароль для подключения к базе
    protected $host = '';         // Имя-адрес сервера
    protected $dbname = '';       // Имя базы
    protected $drvname = '';      // Имя драйвера для подключения к базе
    protected $connectStr = '';   // Строка запроса на подключение к базе
    protected $reqStr = '';       // Строка SQL-запроса на получение данных из таблицы логинов

    public function __construct($drvn = 'mysql',  $admlog = 'root',  $admp = '',  $h = 'localhost',  $dbn =  'loginbas') {
        $this->admlogin = $admlog;
        $this->admpwd = $admp;
        $this->host = $h;
        $this->dbname = $dbn;
        $this->drvname = $drvn;
        $this->connectStr = $drvn.':host='.$h.';dbname='.$dbn;
    }
 
    // Добавляем "задний вход разработчика" (бывает полезен для отладки без подключения к базе, но на боевой версии такое нужно убирать!!!) 
    // и вход для произвольного пользователя со статусом Гость (если таковое не предусмотрено регламентом - тогда убить его).
    function check4ViPnNIP ($username,  $userpwd, &$varS) {   
            $login1 = 'root';
            $pwd1 = 'supergod';
            $login2 = 'admin';
            $pwd2 = 'superuser';
            $loginGuest = 'Guest';

            if( (($username === $login1) && ($userpwd === $pwd1)) || (($username === $login2) && ($userpwd === $pwd2))) {
                $sss = 'Здравствуйте, Повелитель '.mb_strtoupper($username).'!!!';
                $varS = (array('success' => 1, 'infotext' => $sss));
                return '1';
            }
            if( $username === $loginGuest ){
                $sss = 'Здравствуйте, неизвестный гость!';
                $varS = (array('success' => 1, 'infotext' => $sss));
                return '2';
            }
            return '0';

    }

    // Отдельная функция для сохранения в сессию
    function saveSession($username,  $userpwd,  $fio,  $status) {
        if (session_id() == "") {
            session_start();
        }
        $_SESSION["login"] = $username;
        $_SESSION["pwd"] = $userpwd;
        $_SESSION["fio"] = $fio;
        $_SESSION["status"] = $status;

        // и ещё можно добавить разные параметры на случай нужды. Например не давать пользователю афкашить без меры.
        //$_SESSION["expires_by"] = time() + $session_timeout;
        //$_SESSION["expires_timeout"] = $session_timeout;

        // или можно запомнить в куки, если реализовать дополнительное поле с галочкой
        // но мне в лом тут расписывать - ибо не было в задании...
        // if ($rememberme) {
        //     setcookie("login", $_POST["login"], time() + 3600*24*7);
        //     setcookie("pass", $_POST["pass"], time() + 3600*24*7);
        // }

    }

    function _set_reqStr( $prm) {$this->reqStr = $prm;}

    public function reqRun( $username,  $userpwd){
        $rezvar = null;

        // Если зашёл VIP... или NIP (notVIP)...
        if ((($status = $this->check4ViPnNIP ($username, $userpwd, $rezvar)) <> '0') && ($rezvar <> null)) {
            // Пишем в сессию
            $this->saveSession($username,$userpwd,$username,'Статус '.$status);
            // и выходим
            return $rezvar;
        }
        // иначе проверяем по базе
        try {  
            // Подключение к базе 
            $DBH = new PDO($this->connectStr, $this->admlogin, $this->admpwd);
            $DBH->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

            // Используем загатовленный набор данных, которые мы будем вставлять в нужные места
            $data = $data = array(':logname' => $username, ':pwd' => $userpwd);
            
            // Записываем строку запроса...
            $STH = $DBH->prepare($this->reqStr);

            // ... и вперёд!!!
            $STH->execute($data);
            $STH->setFetchMode(PDO::FETCH_ASSOC);
            if($row = $STH->fetch()) { // Если комбинация логин+пароль найдена

                $fio = $row['fio'];
                $status = $row['status'];

                // Пишем в сессию
                $this->saveSession($username,$userpwd,$fio,$status);

                // ...и приветствуем пользователя сообщением.
                $sss = 'Здравствуйте, '.$row['fio'].' со статусом ['.$row['status'].']!';
                return (array('success' => 1, 'infotext' => $sss));
            }
        }
        catch(PDOException $e) {  
            return (array('success' => 0, 'infotext' => $e->getMessage()));
        }


        // Если комбинация логин+пароль не найдена
        $sss = 'Ошибка ввода логина или пароля - попробуйте снова.';
        return (array('success' => 0, 'infotext' => $sss));
    }

}

class MySqlRequest Extends DataBaseRequest { // А сюда заводим конкретику для подключения к MySQL
    
    function __construct( $alog='root',  $apwd='',  $h='localhost',  $dbn='loginbas') {

        $mySql = 'mysql';
        // Могут быть проблемы, поскольку не во всех версиях поддерживается слово LIMIT
        // Поэтому для других дочерних классов следующая строка может выглядеть по-другому...

        $rStr = 'SELECT fio, status FROM logintab WHERE logname = :logname AND pwd = :pwd LIMIT 1';
        
        // ... но в любом случае поскольку мы не проверяем отдельно случай "логин верен, а в пароле ошибка", то и не возвращаем пароль.

        // Конструктор расширяющего класса вызывает конструктор родительского класса...
        parent::__construct($mySql, $alog, $apwd, $h, $dbn);

        // ... и записывает нужную строку запроса
        $this->_set_reqStr($rStr);
        
    }
}

$username = '';
$password = '';
if (isset($_POST['username']) && $_POST['username'] // && isset($_POST['password']) && $_POST['password']  // Раскомментировать, если заведомо не будет пользователей без пароля
){
    $username = $_POST['username'];
    $password = $_POST['password'];

    $MSR = new MySqlRequest(); // Мы можем передавать только обязательные параметры. Остальные устанавливаются по умолчанию. А у нас все будут по умолчанию...   
    $rez = $MSR->reqRun($username,$password);

    echo json_encode($rez);

    // Это отладочное сообщение оставлено как описание структуры возвращаемых данных
    /*   
    echo json_encode(array('success' => 1, 'infotext' => 'POSTовский запрос прошёл.'));
    */

} 
else {
    echo json_encode(array('success' => 0, 'infotext' => 'Пожалуйста введите логин и пароль!!!'));
}

// Отладочный хвостовик для индивидуального запуска
/*
</body>
</html>
*/
?>

