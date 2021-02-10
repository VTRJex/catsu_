<?php session_start();
// PHP inclusions are jank.
set_include_path(dirname(__DIR__));

// Config
$config = include("config.php");

// Database
include "assets/database/database.php";

// Privileges
include "assets/objects/privileges.php";

// Try session login.
include "assets/objects/account.php";
$account->sessionLogin();

// We're not authenticated; Redirect to login.
if (!$account->isAuthenticated()) {
    $_SESSION["msg"] = array(
        "type" => "danger",
        "msg" => "Woah! You must be logged in to access that page!"
    );
    return header("Location: /login.php");

// We're authenticated, but we don't have enough privileges to access this page; Redirect home.
} else if ($account->isAuthenticated() && !($account->getPrivileges() & Privileges::Staff)) {
    $_SESSION["msg"] = array(
        "type" => "danger",
        "msg" => "Woah <b>" . $account->getUsername() . "</b>! Your clearance isn't high enough to access that page!"
    );
    return header("Location: /");
}

/*
Message
Try and get session defined message
(usually set on login), otherwise default
to empty message array then NULL out session message.
*/
$msg = $_SESSION["msg"] ?? array(
    "type" => "", 
    "msg" => ""
);
unset($_SESSION["msg"]);
?>

<html>
  <head>
    <!-- Title -->
    <title><?= $config->instanceName ?> - Admin - Dashboard</title>

    <!-- JQuery and Popper -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/e5971878b8.js" crossorigin="anonymous"></script>

    <!-- Custom Style -->
    <link rel="stylesheet" href="../style.css">
  </head>
  <body>
    <!-- Header -->
    <?php include "assets/content/header.php" ?>

    <!-- Content -->
    <div class="container-fluid">
      <?php if (array_filter($msg)) : ?>
      <div class="alert alert-<?= $msg["type"] ?> mt-3 mb-0" role="alert">
        <?= $msg["msg"] ?>
      </div>
      <?php endif; ?>
      <div class="row">
        <!-- Sidebar -->
        <?php include "assets/content/sidebar.php" ?>
        <div class="col mt-1 mb-1 pt-3 pb-5 pl-5 pr-5 bg-dark text-white">
          <h3>Dashboard</h3>
          <p>This is the <b>Dashboard</b>! The home of <b><?= $config->instanceName ?></b>'s Admin Panel!</p>
          <hr/>
          <div class="admin-stats text-center">
            <!-- TODO: Get endpoint properly 4head
            div class="btn btn-success">
                <div>
                    <h3><?= json_decode(file_get_contents("http://localhost/api/get_online"))->online ?? 0 ?></h3>
                </div>
                <div>
                    Online Users
                </div>
            </div>
            -->
            <div class="btn btn-primary">
                <div>
                    <h3><?= $db->query("SELECT COUNT(*)-1 total FROM users;")->fetch_assoc()["total"] ?? 0 ?></h3>
                </div>
                <div>
                    Registered Users
                </div>
            </div>
            <div class="btn btn-secondary">
                <div>
                    <h3><?= $db->query("SELECT CEILING(SUM(pp)) total FROM (SELECT pp FROM scores_vn UNION ALL SELECT pp FROM scores_rx UNION ALL SELECT pp FROM scores_ap) t GROUP BY pp;")->fetch_assoc()["total"] ?? 0 ?></h3>
                </div>
                <div>
                    Total PP
                </div>
            </div>
            <div class="btn btn-info">
                <div>
                    <h3><?= $db->query("SELECT SUM(id) total FROM (SELECT id FROM scores_vn UNION ALL SELECT id FROM scores_rx UNION ALL SELECT id FROM scores_ap) t GROUP BY id;")->fetch_assoc()["total"] ?? 0 ?></h3>
                </div>
                <div>
                    Total Scores
                </div>
            </div>
            <div class="btn btn-warning">
                <div>
                    <h3><?= $db->query("SELECT SUM(plays_vn_std + plays_vn_taiko + plays_vn_catch + plays_vn_mania + plays_rx_std + plays_rx_taiko + plays_rx_catch + plays_ap_std) total FROM stats;")->fetch_assoc()["total"] ?? 0 ?></h3>
                </div>
                <div>
                    Total Plays
                </div>
            </div>
          </div>
          <hr />
          <div>
            TODO
          </div>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <?php include "assets/content/footer.php" ?>
  </body>
</html>
