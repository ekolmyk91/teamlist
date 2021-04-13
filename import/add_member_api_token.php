<?php

require_once 'config.php';

fill_table_api_token_for_memebers();

function fill_table_api_token_for_memebers() {
    global $dbh;
    $user_ids = $dbh->query("SELECT id FROM users")->fetchAll(PDO::FETCH_COLUMN);
    $sql = "UPDATE users SET api_token=:api_token WHERE id=:id";
    $stmt= $dbh->prepare($sql);
    foreach ($user_ids as $id) {
        $api_token = random_strings(60);
        $stmt->bindParam(':api_token', $api_token);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}

function random_strings($length_of_string)
{
    $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

    return substr(str_shuffle($str_result), 0, $length_of_string);
}
