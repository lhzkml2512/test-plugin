<?php
/*
Plugin Name: 测试插件
Description: 用于测试WordPress插件开发的示例插件
Version: 1.0
Author: 您的名字
Author URI: 您的网站地址
*/
// 获取插件的当前版本号
function get_plugin_version() {
    $plugin_data = get_file_data( __FILE__, array( 'Version' => 'Version' ) );
    return $plugin_data['Version'];
}

// 检查是否有新的发行版本可用
function check_for_plugin_update() {
    $api_url = 'https://api.github.com/repos/your-username/your-plugin-repo/releases/latest';
    $response = wp_remote_get($api_url);
    
    if (is_wp_error($response)) {
        return false;
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if ($data && isset($data['tag_name'])) {
        $latest_version = $data['tag_name'];
        
        if (version_compare($latest_version, get_plugin_version(), '>')) {
            return $latest_version;
        }
    }
    
    return false;
}

// 在插件初始化时检查是否有新的发行版本可用
function my_plugin_check_update_on_init() {
    $latest_version = check_for_plugin_update();
    
    if ($latest_version) {
        add_action('admin_notices', 'my_plugin_display_update_notice');
    }
}
add_action('init', 'my_plugin_check_update_on_init');

// 显示更新通知
function my_plugin_display_update_notice() {
    $latest_version = check_for_plugin_update();
    
    if ($latest_version) {
        $update_url = admin_url('admin-post.php?action=my_plugin_do_update');
        $message = sprintf(__('There is a new version of My Plugin available (%s). <a href="%s">Update Now</a>'), $latest_version, $update_url);
        
        echo '<div class="notice notice-info is-dismissible"><p>' . $message . '</p></div>';
    }
}

// 执行更新操作
function my_plugin_do_update() {
    $latest_version = check_for_plugin_update();
    
    if ($latest_version) {
        // 下载最新的发行版本文件
        $download_url = 'https://github.com/your-username/your-plugin-repo/archive/' . $latest_version . '.zip';
        $temp_file = download_url($download_url);
        
        if (is_wp_error($temp_file)) {
            wp_die('Failed to download the update file.');
        }
        
        // 解压缩文件
        $unzip_result = unzip_file($temp_file, WP_PLUGIN_DIR);
        
        if (is_wp_error($unzip_result)) {
            wp_die('Failed to extract the update file.');
        }
        
        // 删除临时文件
        unlink($temp_file);
        
        // 更新成功后重定向到插件设置页面
        wp_redirect(admin_url('options-general.php?page=my-plugin-settings'));
        exit;
    }
}
add_action('admin_post_my_plugin_do_update', 'my_plugin_do_update');


// 在WordPress加载插件时触发的函数
function test_plugin_activation() {
    // 在这里可以执行任何在插件激活时需要进行的操作
    // 例如创建数据库表格、添加默认设置等等
}

// 在WordPress卸载插件时触发的函数
function test_plugin_deactivation() {
    // 在这里可以执行任何在插件卸载时需要进行的操作
    // 例如删除数据库表格、清理设置等等
}

// 在WordPress管理后台显示插件设置页面的函数
function test_plugin_settings_page() {
    // 在这里可以添加您的插件设置页面的HTML代码
    echo "<h2>测试插件设置</h2>";
    echo "<p>这是一个测试插件的设置页面。</p>";
}

// 注册插件激活和卸载的钩子
register_activation_hook( __FILE__, 'test_plugin_activation' );
register_deactivation_hook( __FILE__, 'test_plugin_deactivation' );

// 添加插件设置菜单
add_action( 'admin_menu', 'test_plugin_add_settings_menu' );
function test_plugin_add_settings_menu() {
    add_options_page( '测试插件设置', '测试插件', 'manage_options', 'test-plugin', 'test_plugin_settings_page' );
}