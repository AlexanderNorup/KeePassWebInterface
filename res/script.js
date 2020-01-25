
var currentEntryUUID = "";
var currentEntryElement;
var QRScanner;
var cameras = [];
function revealPassword(entryUUID, element){
    currentEntryUUID = entryUUID;
    currentEntryElement = element;
    $("#decryptPasswordModal").modal();
    $("#masterPasswordInput").show();
    $("#decryptingWaiter").hide();
    $("#decryptingError").hide();
    $("#decryptingErrorValue").text("");
    $("#decryptingWaiting").show();
    $("#decryptPasswordButton").show();

}


function ScanQR(input){
    $("#scanQRModal").modal();


    QRScanner = new Instascan.Scanner({ video: document.getElementById('video-preview') });
    QRScanner.addListener('scan', function (content) {
        $("#"+input).val(content);
        $('#scanQRModal').modal('hide');
    });
    Instascan.Camera.getCameras().then(function (cameras_) {
        cameras = cameras_;
        $("#selectCamera").empty();
        for(var i = 0; i < cameras.length; i++){
            $("#selectCamera").append("<option value='"+i+"'>"+cameras[i].name+"</option>");
        }
        if (cameras.length > 0) {
            $("#noCameraError").hide();
            $("#hasCamera").show();
            QRScanner.start(cameras[0]);
        } else {
            $("#noCameraError").show();
            $("#hasCamera").hide();
        }
    }).catch(function (e) {
        $("#noCameraError").show();
        $("#hasCamera").hide();
    });
}

function changeCamera(){
    var index = $("#selectCamera").val();
    QRScanner.stop();
    QRScanner.start(cameras[index]);
}

$('#scanQRModal').on('hidden.bs.modal', function (e) {
    if(QRScanner != undefined){
        QRScanner.stop();
    }
});

$('#decryptPasswordModal').on('hidden.bs.modal', function (e) {
    $("#master-password-decrypt-password").val(""); //Clear password
});


function copyToClipboard(inputID){
    var copyText = document.getElementById(inputID);

    /* Select the text field */
    copyText.select();
    copyText.setSelectionRange(0, 99999); /*For mobile devices*/

    /* Copy the text inside the text field */
    document.execCommand("copy");
}