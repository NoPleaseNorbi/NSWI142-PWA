<?php
require_once(__DIR__ . '/recodex_lib.php');
function validate() {
    $is_there_error = false;
    $firstName = $lastName = $email = $deliveryBoy = $unboxDay = "";
    $error = [];
    $gifts = [];
    if (empty($_POST["firstName"])) {
        $error[] = "firstName";
        $is_there_error = true;
    } else {
        $firstName = $_POST["firstName"];
        if(!preg_match('/^.{1,100}$/', $firstName)) {
            $error[] = "firstName";
            $is_there_error = true;
        }
    }
    
    if (empty($_POST["lastName"])) {
        $error[] = "lastName";
        $is_there_error = true;
    } else {
        $lastName = $_POST["lastName"];
        if(!preg_match('/^.{1,100}$/', $lastName)) {
            $error[] = "lastName";
            $is_there_error = true;
        }
    }
    if (empty($_POST["email"])) {
        $error[] = "email";
        $is_there_error = true;
    } else {
        $email = $_POST["email"];
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error[] = "email";
            $is_there_error = true;
        }
    }
    
    if (empty($_POST["deliveryBoy"])) {
        $error[] = "deliveryBoy";
        $is_there_error = true;
    } else {
        $deliveryBoy = $_POST["deliveryBoy"];
        if (!preg_match('/jesus|santa|moroz|hogfather|czpost|fedex/', $deliveryBoy)) {
            $error[] = "deliveryBoy";
            $is_there_error = true;
        }
    }
    
    if (empty($_POST["unboxDay"])) {
        $error[] = "unboxDay";
        $is_there_error = true;
    } else {
        $unboxDay = intval($_POST["unboxDay"]);
        if (!preg_match('/24|25/', $unboxDay)) {
            $error[] = "unboxDay";
            $is_there_error = true;
        }
    }
    
    if (!isset($_POST["fromTime"])) {
        $error[] = "fromTime";
        $is_there_error = true;
    } else if ($_POST["fromTime"] == "") {
        $fromTime = null;
    } else {
        $str_time = $_POST["fromTime"];
        if (!preg_match('/^([0-9]|[1][0-9]|[2][0-3]):([0-5][0-9])$/', $str_time)) {
            $error[] = "fromTime";
            $is_there_error = true;
        }
        else {
            $parsed = date_parse($str_time);
            $fromTime = intval($parsed['hour'] * 60 + $parsed['minute']);
        }
    }
    
    if (!isset($_POST["toTime"])) {
        $error[] = "toTime";
        $is_there_error = true;
    } else if ($_POST["toTime"] == "") {
        $toTime = null;
    } else {
        $str_time = $_POST["toTime"];
        if (!preg_match('/^([0-9]|[1][0-9]|[2][0-3]):([0-5][0-9])$/', $str_time)) {
            if (!in_array("toTime", $error)) {
                $error[] = "toTime";
            }
            $is_there_error = true;
        }
        else {
            $parsed = date_parse($str_time);
            $toTime = intval($parsed['hour'] * 60 + $parsed['minute']);
        }
    }
    
    if (empty($_POST["gifts"])) {
        $gifts = [];
    } else {
        $gifts = $_POST["gifts"];
        foreach ($gifts as $key => $value) {
            if(!preg_match('/socks|points|jarnik|cash|teddy|other/', $value)) {
                $error[] = "gifts";
                $is_there_error = true;
            }
        }
    }
    
    if (empty($_POST["giftCustom"])) {
        $giftCustom = null;
        if (in_array('other', $gifts)) {
            $error[] = "giftCustom";
            $is_there_error = true;
        }
    } else {
        $giftCustom = $_POST["giftCustom"];
            if(!preg_match('/^.{1,100}$/', $giftCustom)) {
                $error[] = "giftCustom";
                $is_there_error = true;
            }
            if(!in_array('other', $gifts)) {
                $giftCustom = null;
            }     
    }
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if ($is_there_error) {
            recodex_survey_error("REEEEE", $error);
        }
        else {
            recodex_save_survey($firstName, $lastName, $email, $deliveryBoy, $unboxDay, $fromTime, $toTime, $gifts, $giftCustom);
        }
        header("Location:index.php");  
    }
}
function main() {
    validate();
}
main();
require __DIR__ . '/form_template.html';