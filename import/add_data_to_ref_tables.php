<?php
require_once 'config.php';

$positions_list = ['HR', 'Front', 'PM', 'Back', 'Office-manager', 'CEO', 'COO', 'QA specialist', 'Marketing specialist', 'Cleaning worker', 'Teamlead', 'Techlead'];
$departments_list = ['Management', 'Magento', 'Wordpress/Drupal', 'Outstaff', 'Cleaning', 'CEO', 'Marketing', 'QA'];

add_data_to_ref_table('positions', $positions_list);
add_data_to_ref_table('departments', $departments_list);

function add_data_to_ref_table($table_name, $name_arr) {
    global $dbh;
    $sql = "INSERT INTO $table_name (name) VALUES (:name)";

    $stmt= $dbh->prepare($sql);
    foreach ($name_arr as $name) {
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->execute();
    }
}
