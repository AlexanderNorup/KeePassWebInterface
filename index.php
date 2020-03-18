<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
//BASE64 IMG VIEWER: <img src="data:image/png;base64,<STRING_HERE>" />
require_once("KeePassWrapper.php");
include("settings.php");
//$kdbxPath = "./Database.kdbx";
$pass = "123";

$db = new KeePassWrapper($kdbxPath);
function getIfSet($entry, $field, $inInput = false)
{
    $toReturn = "";
    if ($inInput) {
        $toReturn = "<div class=\"input-group\">
                <input readonly id=\"" . $entry['UUID'] . "-" . $field . "-id\" type=\"text\" class=\"form-control\"
                       placeholder=\"\" aria-label=\"Field\"
                       aria-describedby=\"" . $entry['UUID'] . "-" . $field . "-addon\" value='";


        //$toReturn = "<input class='form-control' type='text' readonly value='";
        if (isset($entry["StringFields"][$field])) {
            $toReturn .= $entry["StringFields"][$field];
        }
        //$toReturn .= "'/>";
        $toReturn .= "'>
                <div class=\"input-group-append\" id=\"" . $entry['UUID'] . "-" . $field . "-addon\">
                    <button onclick='copyToClipboard(\"" . $entry['UUID'] . "-" . $field . "-id\")' class=\"btn btn-outline-secondary\" type=\"button\">COPY</button>
                </div>
            </div>";
    } else {
        if (isset($entry["StringFields"][$field])) {
            $toReturn = $entry["StringFields"][$field];
        } else {
            $toReturn = "<i class='nothing'>Nothing</i>";
        }
    }
    return $toReturn;
}

?>
<html>
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KeePass Web interface</title>
    <link rel="stylesheet" href="res/style.css"/>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"
          integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <script src="https://kit.fontawesome.com/1b96034306.js" crossorigin="anonymous"></script>
</head>
<body>


<div class="container main">

    <div class="jumbotron">
        <h1>Keepass Webinterface</h1>
        <h4>By Alexander Nørup</h4>
        <h6>Version 1.1<sup> <a href="javascript:triggerChangelog();">Changelog</a></sup></h6>
        <?php if ($err = $db->getLastError()): ?>
            <div class="alert alert-danger" role="alert">
                <strong>Error!</strong> <?php echo $err; ?>
            </div>
        <?php else: ?>
            <?php if ($db->hasIndex()): ?>
                <?php
                $index = $db->getIndex();
                ?>
                <p>Database index created: <?php echo date("F d, Y h:i:s A", $index["CreationTime"]); ?>
                <div class="table-responsive" style="overflow-x: visible;">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th scope="col">
                                Icon
                            </th>
                            <th scope="col">
                                Name
                            </th>
                            <th scope="col">
                                Username
                            </th>
                            <th scope="col">
                                Password
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($index["Groups"]["Groups"] as $group): ?>
                        <?php if($group["Name"] == "Recycle Bin") continue; ?>
                            <tr>
                                <th class="center" colspan="4"><?php echo $group["Name"]; ?></th>
                            </tr>
                            <?php if (isset($group["Entries"])) foreach ($group["Entries"] as $entry): ?>
                                <tr>
                                    <td><?php echo isset($entry["CustomIconUUID"]) ? "<img class=\"entry-icon\" src=\"data:image/png;base64," . $index["CustomIcons"][$entry["CustomIconUUID"]] . "\" />" : "<i class=\"fas fa-key entry-icon\"></i>" ?></td>
                                    <td><?php echo getIfSet($entry, "Title"); ?></td>
                                    <td><?php echo getIfSet($entry, "UserName", true); ?></td>
                                    <td>
                                        <button onclick="revealPassword('<?php echo $entry["UUID"]; ?>', this);" type="button" class="btn btn-light btn-block">Click to reveal</button>
                                        <div class="entry-password-box">
                                            <?php //echo getIfSet($entry, "PASSWORD", true); ?>

                                            <div class="input-group">
                                                <input readonly="" id="<?php echo $entry["UUID"]; ?>-PASSWORD-id" type="text" class="form-control" placeholder="" aria-label="Field" aria-describedby="<?php echo $entry["UUID"]; ?>-PASSWORD-addon" value="">
                                                <div class="input-group-append" id="<?php echo $entry["UUID"]; ?>-PASSWORD-addon">
                                                    <button onclick="copyToClipboard('<?php echo $entry["UUID"]; ?>-PASSWORD-id');" class="btn btn-outline-secondary" type="button">COPY</button>
                                                    <button onclick="triggerMoreInfo('<?php echo $entry["UUID"]; ?>');" class="btn btn-outline-secondary" type="button"><i class="fas fa-info-circle"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>There is no avaliable index. Please refresh one, by unlocking the database below!</p>
            <?php endif; //END: check if index exists. ?>
            <hr>
            <label for="master-password-refresh-index">Refresh index:</label>
            <div class="input-group">
                <input id="master-password-refresh-index" type="password" class="form-control"
                       placeholder="Master Password" aria-label="Master Password input"
                       aria-describedby="button-refreshIndex-addon"
                       enterTriggers="refreshIndex();">
                <div class="input-group-append" id="button-refreshIndex-addon">
                    <button onclick="refreshIndex();" class="btn btn-outline-secondary" type="button">Unlock</button>
                    <button onclick="ScanQR('master-password-refresh-index');" class="btn btn-outline-secondary" type="button"><i class="fas fa-qrcode"></i></button>
                </div>
            </div>

        <?php endif; //END: check if database exists. ?>
    </div>

</div>

<!-- MODALS -->

<?php include("InsertPasswordModal.html"); ?>
<?php include("ScanQRModal.html"); ?>
<?php include("RefreshIndexWaiter.html"); ?>
<?php include("MoreInfoModal.html"); ?>
<?php include("Changelog.html"); ?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
        integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo"
        crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"
        integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6"
        crossorigin="anonymous"></script>
<script type="text/javascript" src="res/instascan.min.js"></script>
<script src="res/script.js"></script>
<script src="res/api.js"></script>
</body>
</html>

