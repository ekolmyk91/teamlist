<?php
require_once 'config.php';

fill_table_user_id_role_memeber_id();
function fill_table_user_id_role_memeber_id() {
    global $dbh;
    $user_ids = $dbh->query("SELECT user_id FROM members")->fetchAll(PDO::FETCH_COLUMN);
    $sql = "INSERT INTO role_user (user_id, role_id) VALUES (:user_id, 2)";
    $stmt= $dbh->prepare($sql);
    foreach ($user_ids as $user_id) {
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
    }
}
