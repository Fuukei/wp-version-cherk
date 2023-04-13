<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 同步时间为服务器时间
    date_default_timezone_set('UTC');
    $date = date('Y-m-d H:i:s', time());

    // 防止SQL注入攻击和XSS攻击
    $version = htmlspecialchars($_POST['version'], ENT_QUOTES, 'utf-8');

    // 防止文件包含攻击
    $file = realpath(dirname(__FILE__)) . '/data.txt';
    if (strpos($file, realpath(dirname(__FILE__))) !== 0) {
        error_log('Attempt to read file outside upload directory: ' . $file);
        exit;
    }

    // 将数据存储到文件中
    if($handle = fopen($file, 'a')) {
        $data = "$date|$version\n"; // 拼接数据
        if(!fwrite($handle, $data)) {
            error_log('Unable to write to file: ' . $file);
        }
        fclose($handle);
    } else {
        error_log('Unable to open file: ' . $file);
    }
}

// 输出版本统计结果
$file = realpath(dirname(__FILE__)) . '/data.txt';
if (file_exists($file)) {
    $data = file($file);

    // 同步时间为服务器时间
    date_default_timezone_set('UTC');
    $server_time = time();

    $versions = array_map(function($line) use ($server_time) {
        list($date, $version) = explode('|', $line);
        $compensated_date = date('Y-m-d H:i:s', strtotime($date, $server_time - strtotime($_SERVER['REQUEST_TIME'])));
        if (strtotime($compensated_date) >= strtotime('-24 hours')) {
            return htmlspecialchars($version, ENT_QUOTES, 'utf-8');
        }
    }, $data);
    $count = array_count_values(array_filter($versions));
    asort($count);

    echo '<style>
        table {
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
        }
    </style>';

    echo '<table><tr><th>Version</th><th>Count</th></tr>';
    foreach ($count as $version => $c) {
        echo "<tr><td>$version</td><td>$c</td></tr>";
    }
    echo '</table>';
}
?>