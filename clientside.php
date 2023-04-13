<?php
// 函数：发送主题版本号
function send_theme_version() {
    $theme = wp_get_theme();
    $version = $theme->get('Version');
    $data = array(
        'date' => date('Y-m-d H:i:s'),
        'version' => $version
    );
    $args = array(
        'body' => $data,
        'timeout' => '5',
        'redirection' => '5',
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => array(),
        'cookies' => array()
    );
    wp_remote_post('https://api.maho.cc/ver-stat/index.php', $args);
}
// 每小时执行一次
if (!wp_next_scheduled('my_hourly_event')) {
    wp_schedule_event(time(), 'hourly', 'my_hourly_event');
}
add_action('my_hourly_event', 'send_theme_version');
?>
