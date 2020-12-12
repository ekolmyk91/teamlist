<?php
require_once 'config.php';
$file_csv = 'import/department.csv';
import($file_csv);

function import($file) {
    $user_list = export_csv_to_array($file);
    $user_list = remove_spaces($user_list);
    save_images($user_list);
    $users = data_prepare($user_list);
    save_users($users);
}

function export_csv_to_array($file) {
    $data_array = [];
    if (($h = fopen("{$file}", "r")) !== FALSE)
    {
        while (($data = fgetcsv($h, 1000, ",")) !== FALSE)
        {
            $data_array[] = $data;
        }
        fclose($h);
    }

    return $data_array;
}

function remove_spaces($data_array) {
    foreach ($data_array as $id => $users) {
        foreach ($users as $key => $property) {
            $data_array[$id][$key] = trim($data_array[$id][$key]);
        }
    }

    return $data_array;
}

function data_prepare($array) {
    foreach ($array as $key => $items) {
        //separate phone number
        $number = preg_replace('/[^0-9]/', '', $array[$key][3]);
        if (strlen($number) > 10) {
            $array[$key][3] = substr($number, 0, 10);
            $array[$key][8] = substr($number, 10);
        } else {
            $array[$key][3] = $number;
        }
        //add position's and department's id
        $array[$key][4] = change_department_to_id($array[$key][4]);
        $array[$key][5] = change_position_to_id($array[$key][5]);
        //add default photo
        $array[$key][6] = (!empty($array[$key][6])) ? substr($array[$key][6], 43).'.jpg' : 'default_user.jpg';
    }
    return $array;
}

function change_department_to_id($item_name) {
    $departments = get_departments();
    foreach ($departments as $id => $department) {
        if ($item_name == $department) {

            return $id;
        }
    }
}

function change_position_to_id($item_name) {
    $positions = get_positions();
    foreach ($positions as $id => $position) {
        if ($item_name == $position) {

            return $id;
        }
    }
}

function get_positions() {
    global $dbh;
    $positions = $dbh->query("SELECT * FROM positions")->fetchAll(PDO::FETCH_KEY_PAIR);

    return $positions;
}

function get_departments() {
    global $dbh;
    $departments = $dbh->query("SELECT * FROM departments")->fetchAll(PDO::FETCH_KEY_PAIR);

    return $departments;
}

function save_users($users_data) {
    global $dbh;
    try {
        $dbh->beginTransaction();
        foreach ($users_data as $key => $param) {

            $name          = $users_data[$key][0];
            $surname       = $users_data[$key][1];
            $email         = $users_data[$key][2];
            $phone_1       = $users_data[$key][3];
            $department_id = $users_data[$key][4];
            $position_id   = $users_data[$key][5];
            $avatar        = $users_data[$key][6];
            $birthday      = date('Y-m-d H:i:s', strtotime($users_data[$key][7]));
            $phone_2       = (isset($users_data[$key][8])) ? $users_data[$key][8] : null;
            $password      = password_hash("password", PASSWORD_BCRYPT);

            $dbh->exec("insert into users (name, email, password, avatar, active)
                                    values (\"$name\", \"$email\", \"$password\", \"$avatar\", 1)");
            $user_id = $dbh->query("select id from users where email = \"$email\"")->fetchColumn();
            $dbh->exec("insert into members (user_id, name, surname, birthday, email, phone_1, phone_2, department_id, position_id)
                                        values (\"$user_id\", \"$name\", \"$surname\", \"$birthday\", \"$email\",
                                                \"$phone_1\", \"$phone_2\", \"$department_id\", \"$position_id\")");

        }
        $dbh->commit();
    } catch (Exception $e) {
        $dbh->rollBack();
        echo "Error: " . $e->getMessage();
    }
}

function save_images($arr) {
    foreach ($arr as $key=>$property) {
        $url = substr($property[6], 43);
        $path = 'storage/app/public/avatar/'.substr($property[6], 43).'.jpg';
        if ($url) {
            shell_exec("sh import/export.sh $url $path");
            }
        }
}