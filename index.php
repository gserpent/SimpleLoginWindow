<!doctype html>
<html>
<head>
<title>Окно регистрации с использованием "ванильного" JavaScript</title>
<meta http-equiv="Content-Language" content="ru">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>

<form id="JSform">
    <div>
        Имя пользователя:
        <input type="text" name="username" id="username" /><br />
        Пароль:
        <input type="password" name="password" id="password" />  <br />  
        <input type="button" name="loginBtn" id="loginBtn" value="Войти" OnClick="javascript: sendMyRequest();" />
    </div>
</form>
<br/>
<div id="myinfo"> 
        Введите логин и пароль.
</div>
<br/>

<script type="text/javascript">
function sendMyRequest() {
    
    const $elem = document.querySelector('#myinfo');
    $elem.textContent = 'Готовлю запрос к базе, ждите...';

    

    var xhr = new XMLHttpRequest();     // Создаём экземпляр запроса

    var formData = new FormData(document.forms.JSform);     // экземпляр FormData с данными из формы

    // Создаём обработчики
    xhr.onerror = function() {
        $elem.textContent = "ОШИБКА ВЫПОЛНЕНИЯ ЗАПРОСА! Попробуйте позднее.";
    };

    xhr.onload = function() {
        if(xhr.readyState === 4) {      // Запрос как-то завершился.
            if(xhr.status === 200) {    // Успешное выполнение запроса.
                var responseObj = xhr.response;     // Возвращены данные в формате JSON

                if(responseObj.success === 1) {
                    $elem.textContent = responseObj.infotext;
                } 
                else {
                    $elem.textContent = "ОШИБКА:" + responseObj.infotext;
                }
            }
            else { // Что-то пошло не так.
                $elem.textContent = "ОШИБКА ВЫПОЛНЕНИЯ ЗАПРОСА! Попробуйте позднее.";
            }
        }
    }

    $elem.textContent = 'Стартую запрос к базе, ждите...';
    // ...и стартуем асинхронный запрос.
    xhr.open('POST', 'verify.php', true)
    xhr.responseType = 'json';
    xhr.send(formData);

}
</script>

</body>
</html>