<?php
function checkUserRole($role) {
    session_start();
    if (!isset($_SESSION['role']) || $_SESSION['role'] != $role) {
        header("Location: ../login.php");
        exit();
    }
}
