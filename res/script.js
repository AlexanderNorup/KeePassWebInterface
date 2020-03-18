String.prototype.replaceAll = function(target, replacement) {
    return this.split(target).join(replacement);
};
function linkify(text) {
    var urlRegex =/(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
    return text.replace(urlRegex, function(url) {
        return '<a target="_blank" href="' + url + '">' + url + '</a>';
    });
}

var currentEntryUUID = "";
var currentEntryElement;
var QRScanner;
var cameras = [];

var storedData = [];

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

$(document).ready(function(){
    //Add [EnterTrigger] attribute support.
    $("[enterTriggers]").each(function(){
        $(this).keypress(function(event){
           if(event.code == "Enter"){
               eval($(this).attr("enterTriggers")); //Runs JS in attribute
           }
        });
    });
});

function triggerMoreInfo(entryUUID){
    $("#moreInfoModal").modal();

    $("#moreInfoTableBody").empty();
    var toAppend = "";
    if(storedData[entryUUID] != undefined) {
        for (var i in storedData[entryUUID]) {
            toAppend += "<tr>";
            toAppend += "<td>" + i + "</td>";
            toAppend += "<td class='wordBreak'>" + linkify(storedData[entryUUID][i].replaceAll("\n", "<br>") ) + "</td>";
            toAppend += "</tr>";
        }
    }
    $("#moreInfoTableBody").append(toAppend);
}

function triggerChangelog(){
    $("#changelogModal").modal();
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