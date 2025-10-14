<?php
session_start();

define('DATA_DIR', __DIR__ . '/../data/');
define('USERS_FILE', DATA_DIR . 'users.json');
define('TRACKER_FILE', DATA_DIR . 'tracker.json');
define('WORKOUTS_FILE', DATA_DIR . 'workouts.json');

function readJSON($file) {
    if (!file_exists($file)) {
        return [];
    }
    $content = file_get_contents($file);
    return json_decode($content, true) ?: [];
}

function writeJSON($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getLoggedInUser() {
    if (!isLoggedIn()) return null;
    $users = readJSON(USERS_FILE);
    foreach ($users as $user) {
        if ($user['id'] == $_SESSION['user_id']) {
            return $user;
        }
    }
    return null;
}

function redirect($page) {
    header("Location: $page");
    exit();
}
?>