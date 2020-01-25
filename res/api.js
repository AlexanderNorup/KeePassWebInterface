String.prototype.startsWith = function (str) {
    return !this.indexOf(str);
}
//Handles all API calls

function refreshIndex(){
    $("#refreshIndexError").hide();
    $("#refreshIndexErrorValue").text("");
    $("#refreshIndexWaiter").show();
    var password = $("#master-password-refresh-index").val();
    $("#master-password-refresh-index").val("");
    if(password.trim() === ""){return;}
    $("#refreshIndexModal").modal();
    $.post("api.php", {
        action: "refreshIndex",
        masterPassword: password
    }, function(resp){
        console.log(resp);
        if(resp === "OK!"){
            location.reload();
        }else{
            $("#refreshIndexError").show();
            $("#refreshIndexErrorValue").text(resp);
            $("#refreshIndexWaiter").hide();
        }
    });
}

function getPassword(){

    var password = $("#master-password-decrypt-password").val();
    $("#master-password-decrypt-password").val("");
    if(password.trim() === ""){return;}

    $("#masterPasswordInput").hide();
    $("#decryptingWaiter").show();

    var button = $(currentEntryElement);
    var inputGroup = $(currentEntryElement).parent().find(".entry-password-box").first();
    var inputBox = inputGroup.find("input").first();
    $("#decryptPasswordButton").hide();


    $.post("api.php", {
        action: "getPassword",
        masterPassword: password,
        entryUUID: currentEntryUUID
    }, function(resp){
        if(resp.startsWith("ERROR")){
            $("#decryptingError").show();
            $("#decryptingErrorValue").text(resp);
            $("#decryptingWaiting").hide();
        }else{
            inputGroup.addClass("show");
            button.hide();
            inputBox.val(resp);
            $("#decryptPasswordModal").modal('hide');
        }
    });
}