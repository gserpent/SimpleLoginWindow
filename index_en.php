<!doctype html>
<html>
<head>
<title>Registration window with use of "vanilla" JavaScript</title>
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
        Enter login and password.
</div>
<br/>

<script type="text/javascript">
function sendMyRequest() {
    
    const $elem = document.querySelector('#myinfo');
    $elem.textContent = 'Database request in process, please wait...';

    

    var xhr = new XMLHttpRequest();     // Request object creation

    var formData = new FormData(document.forms.JSform);     // FormData object with the form data

    // Создаём обработчики
    xhr.onerror = function() {
        $elem.textContent = "Reqest proceading error! Please try again later.";
    };

    xhr.onload = function() {
        if(xhr.readyState === 4) {      // The request is fulfilled somehow.
            if(xhr.status === 200) {    // The request is fulfilled with success.
                var responseObj = xhr.response;     // JSON formatted data have been returned

                if(responseObj.success === 1) {
                    $elem.textContent = responseObj.infotext;
                } 
                else {
                    $elem.textContent = "ERROR:" + responseObj.infotext;
                }
            }
            else { // Something ran wrong.
                $elem.textContent = "Request fulfilling error! Please try later.";
            }
        }
    }

    $elem.textContent = 'Database request is starting, please wait......';
    // ...and we start the asynchronious request.
    xhr.open('POST', 'verify.php', true)
    xhr.responseType = 'json';
    xhr.send(formData);

}
</script>

</body>
</html>