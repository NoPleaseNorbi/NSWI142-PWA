<?php
function prepare_variables($input_array, $page) {
    file_put_contents("variables.php", "<?php
");
    $parameters_page = preg_replace('/templates/', 'parameters', $page);
    $variables = include($parameters_page);
    foreach ($input_array as $key => $value) {
        foreach ($variables as $import_key => $import_value) {
            if ($key == $import_key) {
                if (is_array($import_value)) {
                    $found = false;
                    foreach ($import_value as $import_import_key => $import_import_value) {
                        if ($value == $import_import_value) {
                            $found = true;
                            $$key = $input_array[$key];
                            $current = file_get_contents("variables.php");
                            $current .= "$$key = " . "\"$value\"". ";";
                            file_put_contents("variables.php", 
                                $current);
                        }
                    }
                    if (!$found) {
                        http_response_code(400);
                    }
                } 
                if ($import_value == 'int') {
                    if (is_numeric($value)) {
                        $$key = $input_array[$key];
                        $current = file_get_contents("variables.php");
                        $current .= "$$key = " . $value  . ";";
                        file_put_contents("variables.php", 
                            $current);
                    } else {
                        http_response_code(400);
                    }
                }
                if ($import_value == 'string') {
                    if (is_string($value) && !is_numeric($value)) {
                        $$key = $input_array[$key];
                        $current = file_get_contents("variables.php");
                        $current .= "$$key = " . "\"$value\"". ";";
                        file_put_contents("variables.php", 
                            $current);
                    }
                    else {
                        http_response_code(400);
                    }
                }
            }
        }
    }
}

function strap_together_files(){
    if (!isset($_GET['page'])) {
        http_response_code(400);
        exit;
    } else {
        $page = $_GET['page'];
    }
    if (!preg_match('/^([a-zA-Z])+([a-zA-Z]\/?)+([a-zA-Z])+$/', $page)) {
        http_response_code(400);
        exit;
    }

    if(is_dir("templates/".$page)) {
        $page = "templates/" . $page . "/index.php";
    } else {
        $page = "templates/" . $page . ".php";
    }
    if (!file_exists($page)) {
        http_response_code(404);
        exit;
    }
    if (preg_match('/.*php/', $page) ? true : false) {
        include("templates/_header.php");
        if (isset($_SERVER['REQUEST_URI'])) {
            $echo = $_SERVER['REQUEST_URI'];
            parse_str($echo, $output);
            array_shift($output);
            if(!empty($output)) {
                prepare_variables($output, $page);
                include("variables.php");
            }
        }
        include("$page");
        include("templates/_footer.php");
    }
    else {
        http_response_code(400);
        exit;
    }
}

strap_together_files();
?>