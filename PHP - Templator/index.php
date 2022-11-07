<?php

function load_csv($fileName)
{
    $fp = fopen($fileName, "r");
    if (!$fp) {
        throw new Exception("Unable to open $fileName");
    }
    $cols = fgetcsv($fp, 0, ',', '"', '\\');
    $res = [];
    while (($row = fgetcsv($fp, 0, ',', '"', '\\'))) {
        $obj = [];
        foreach ($cols as $idx => $col) {
            $obj[$col] = $row[$idx] ?? null;
        }
        $res[] = (object)$obj;
    }

    fclose($fp);
    return $res;
}

function is_price_valid($price)
{
    return preg_match('/^[0-9]+([.][0-9]{2})?$/', $price);
}

function filter_obj($obj, $search, $maxprice, $instock)
{
    if ($search && strpos($obj->title, $search) === false && strpos($obj->author, $search) === false && strpos($obj->isbn, $search) === false) {
        return false;
    }

    if (is_price_valid($maxprice) && (float)$obj->price > (float)$maxprice) {
        return false;
    }

    if ($instock && $obj->availability !== 'in stock') {
        return false;
    }

    return true;
}

function main()
{
    // (let's make sure we do not pollute global variable namespace)

    // Process URL parameters
    $search = trim($_GET['search'] ?? '');
    $maxprice = trim($_GET['maxprice'] ?? '');
    $instock = $_GET['instock'] ?? false;

    $data = load_csv('./data.csv');
    if ($search || is_price_valid($maxprice) || $instock) {
        $data = array_values(array_filter($data, function ($obj) use ($search, $maxprice, $instock) {
            return filter_obj($obj, $search, $maxprice, $instock);
        }));
    }

    $pagination_count = (int)((count($data) + 9) / 10);
    $pagination_page = max(0, min((int)($_GET['page'] ?? 0), $pagination_count - 1));
    $pagination_link = function ($page) use ($search, $maxprice, $instock) {
        $params = [ 'page' => $page ];
        if ($search) {
            $params['search'] = $search;
        }
        if (is_price_valid($maxprice)) {
            $params['maxprice'] = $maxprice;
        }
        if ($instock) {
            $params['instock'] = 1;
        }
        return http_build_query($params, '', '&amp;');
    };

    if ($pagination_count > 1) {
        $data = array_values(array_slice($data, $pagination_page * 10, 10));
    }

    require __DIR__ . '/template.php';
}

main();
