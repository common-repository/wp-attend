

function createSubscription($event){    
    if(!validateSubscribeForm())
    {
        console.log("invalid");
        return false;
    }
    $event.preventDefault();
    var object = {};
    var formData = new FormData(document.getElementById("subscribeForm"));
    formData.forEach(function(value, key){
        if(key == "name_sub")
            object["name"] = value;
        if(key == "email_sub")
            object["email"] = value;
    });
    var data = JSON.stringify(object);
    var urlRequest = globalObject.homeUrl + '/wp-json/wp-attend/v1/subscription';
    jQuery.ajax(urlRequest, {
        url: urlRequest,
        type: "POST",
        data: data,
        beforeSend: function (xhr) {
            xhr.setRequestHeader("X-WP-Nonce", globalObject.nonce);
        }
    }).done((response) => {
        window.location.href = window.location.href.split('?')[0] + "?subresult=success";
    }).fail(function (data) {
        if(data.status == 400)
            window.location.href = window.location.href.split('?')[0] + "?subresult=" + data.responseJSON['message'];
        else
            window.location.href = window.location.href.split('?')[0] + "?subresult=fail";
    });
}

function validateSubscribeForm(){
    var name = document.forms["subscribeForm"]["name_sub"].value;
    var email = document.forms["subscribeForm"]["email_sub"].value;
    var privacy = document.forms['subscribeForm']['privacy'].value;
    if(name == null || name == '' || !validateEmail(email) || privacy == 'off')
        return false;
    return  true;
}

function validateEmail(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

function confirmDelete(){
    var r = confirm(__('are you sure you want to remove this subscription?', 'wp-attend'));
    return r;
}

function confirmInvalidate() {
    var r = confirm(__('are you sure you want to set this subscription to invalid?', 'wp-attend'));
    return r;
}

function confirmResend(){
    var r = confirm(__('are you sure you want to resend the validation email?', 'wp-attend'));
    return r;
}
