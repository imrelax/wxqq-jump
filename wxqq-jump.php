<?php
/**
 * Plugin Name: 微信QQ防红
 * Plugin URI: http://dalao.run
 * Description: 防止微信和QQ内置浏览器访问，自动跳转到提示页面
 * Version: 1.0.0
 * Author: MR.ZING
 * Author URI: http://dalao.run
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wxqq-jump
 * Domain Path: /languages
 */

// 防止直接访问此文件
if (!defined('ABSPATH')) {
    exit;
}

// 定义插件常量
define('WXQQ_JUMP_VERSION', '1.0.0');
define('WXQQ_JUMP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WXQQ_JUMP_PLUGIN_URL', plugin_dir_url(__FILE__));

// 添加设置页面
function wxqq_jump_add_menu() {
    add_menu_page(
        __('微信QQ防红设置', 'wxqq-jump'),
        __('微信QQ防红', 'wxqq-jump'),
        'manage_options',
        'wxqq-jump',
        'wxqq_jump_options_page',
        'dashicons-shield',
        30
    );
}
add_action('admin_menu', 'wxqq_jump_add_menu', 1);

// 注册设置
function wxqq_jump_settings_init() {
    register_setting('wxqq_jump_options', 'wxqq_jump_settings');
    
    add_settings_section(
        'wxqq_jump_section',
        __('基本设置', 'wxqq-jump'),
        'wxqq_jump_section_callback',
        'wxqq_jump'
    );
    
    add_settings_field(
        'wxqq_jump_enable',
        __('启用防红', 'wxqq-jump'),
        'wxqq_jump_enable_render',
        'wxqq_jump',
        'wxqq_jump_section'
    );
    
    add_settings_field(
        'wxqq_jump_exclude_paths',
        __('排除路径', 'wxqq-jump'),
        'wxqq_jump_exclude_paths_render',
        'wxqq_jump',
        'wxqq_jump_section'
    );
}
add_action('admin_init', 'wxqq_jump_settings_init');

// 设置页面回调函数
function wxqq_jump_section_callback() {
    echo '<p>' . __('请勾选启动按钮。不勾选 不启动。', 'wxqq-jump') . '</p>';
}

function wxqq_jump_enable_render() {
    $options = get_option('wxqq_jump_settings');
    ?>
    <input type="checkbox" name="wxqq_jump_settings[enable]" <?php checked(isset($options['enable']) ? $options['enable'] : 0, 1); ?> value="1">
    <?php
}

function wxqq_jump_exclude_paths_render() {
    $options = get_option('wxqq_jump_settings');
    $exclude_paths = isset($options['exclude_paths']) ? $options['exclude_paths'] : '';
    ?>
    <textarea name="wxqq_jump_settings[exclude_paths]" rows="5" cols="50"><?php echo esc_textarea($exclude_paths); ?></textarea>
    <p class="description"><?php _e('每行一个路径，例如：/wp-admin/', 'wxqq-jump'); ?></p>
    <p class="description"><?php _e('可以是路径 如:/shop', 'wxqq-jump'); ?></p>
    <p class="description"><?php _e('可以是完整网址 如: https://github.com/123.html', 'wxqq-jump'); ?></p>
    <p class="description"><?php _e('可以是相对网址 如/shop/1111.html', 'wxqq-jump'); ?></p>
    <p class="description"><?php _e('如果设置目录 则目录下所有文章都排除。', 'wxqq-jump'); ?></p>
    <p class="description"><?php _e('如果要设置单一文章 请输入完整网址。', 'wxqq-jump'); ?></p>
    <p class="description"><?php _e('留空则全站屏蔽微信QQ浏览器访问。', 'wxqq-jump'); ?></p>
    <?php
}

// 设置页面HTML
function wxqq_jump_options_page() {
    ?>
    <div class="wrap">
        <h2><?php _e('微信QQ防红设置', 'wxqq-jump'); ?></h2>
        <form action='options.php' method='post'>
            <?php
            settings_fields('wxqq_jump_options');
            do_settings_sections('wxqq_jump');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// 主要功能代码
function wxqq_jump_check() {
    $options = get_option('wxqq_jump_settings');
    
    // 检查是否启用
    if (empty($options['enable'])) {
        return;
    }
    
    // 获取当前URL
    $current_url = $_SERVER['REQUEST_URI'];
    
    // 检查是否在排除路径中
    $exclude_paths = isset($options['exclude_paths']) ? $options['exclude_paths'] : '';
    if (!empty($exclude_paths)) {
        $exclude_paths = explode("\n", $exclude_paths);
        foreach ($exclude_paths as $path) {
            $path = trim($path);
            if (!empty($path) && strpos($current_url, $path) !== false) {
                return;
            }
        }
    }
    
    // 检查是否是微信或QQ浏览器
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    if (strpos($user_agent, 'MicroMessenger') !== false || strpos($user_agent, 'QQ/') !== false) {
        $siteurl = home_url($current_url);
        
        // 输出跳转页面
        header('Content-Type: text/html; charset=utf-8');
        echo '<!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>' . __('使用浏览器打开', 'wxqq-jump') . '</title>
            <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport">
            <meta content="yes" name="apple-mobile-web-app-capable">
            <meta content="black" name="apple-mobile-web-app-status-bar-style">
            <meta name="format-detection" content="telephone=no">
            <meta content="false" name="twcClient" id="twcClient">
            <meta name="aplus-touch" content="1">
            <style>
                body,html{width:100%;height:100%}
                *{margin:0;padding:0}
                body{background-color:#fff}
                #browser img{width:50px;}
                #browser{margin: 0px 10px;text-align:center;}
                #contens{font-weight: bold;color: #2466f4;margin:-285px 0px 10px;text-align:center;font-size:20px;margin-bottom: 125px;}
                .top-bar-guidance{font-size:15px;color:#fff;height:70%;line-height:1.8;padding-left:20px;padding-top:20px;background:url(' . esc_url(WXQQ_JUMP_PLUGIN_URL . 'assets/banner.png') . ') center top/contain no-repeat}
                .top-bar-guidance .icon-safari{width:25px;height:25px;vertical-align:middle;margin:0 .2em}
                .app-download-tip{margin:0 auto;width:290px;text-align:center;font-size:15px;color:#2466f4;background:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAAcAQMAAACak0ePAAAABlBMVEUAAAAdYfh+GakkAAAAAXRSTlMAQObYZgAAAA5JREFUCNdjwA8acEkAAAy4AIE4hQq/AAAAAElFTkSuQmCC) left center/auto 15px repeat-x}
                .app-download-tip .guidance-desc{background-color:#fff;padding:0 5px}
                .app-download-tip .icon-sgd{width:25px;height:25px;vertical-align:middle;margin:0 .2em}
                .app-download-btn{display:block;width:214px;height:40px;line-height:40px;margin:18px auto 0 auto;text-align:center;font-size:18px;color:#2466f4;border-radius:20px;border:.5px #2466f4 solid;text-decoration:none}
            </style>
        </head>
        <body>
        <div class="top-bar-guidance">
            <p>' . __('点击右上角', 'wxqq-jump') . '<img src="' . esc_url(WXQQ_JUMP_PLUGIN_URL . 'assets/3dian.png') . '" class="icon-safari">' . __('在 浏览器 打开', 'wxqq-jump') . '</p>
            <p>' . __('苹果设备', 'wxqq-jump') . '<img src="' . esc_url(WXQQ_JUMP_PLUGIN_URL . 'assets/iphone.png') . '" class="icon-safari">' . __('安卓设备', 'wxqq-jump') . '<img src="' . esc_url(WXQQ_JUMP_PLUGIN_URL . 'assets/android.png') . '" class="icon-safari">↗↗↗</p>
        </div>
        <div id="contens">
        <p><br/><br/></p>
        <p>' . __('1.本站不支持 微信或QQ 内访问', 'wxqq-jump') . '</p>
        <p><br/></p>
        <p>' . __('2.请按提示在手机 浏览器 打开', 'wxqq-jump') . '</p>
        </div>
        <div class="app-download-tip">
            <span class="guidance-desc">' . esc_url($siteurl) . '</span>
        </div>
        <p><br/></p>
        <div class="app-download-tip">
            <span class="guidance-desc">' . __('点击右上角', 'wxqq-jump') . '<img src="' . esc_url(WXQQ_JUMP_PLUGIN_URL . 'assets/3dian.png') . '" class="icon-sgd">' . __('or 复制网址自行打开', 'wxqq-jump') . '</span>
        </div>
        <script src="' . esc_url(WXQQ_JUMP_PLUGIN_URL . 'assets/jquery-3.3.1.min.js') . '"></script>
        <script src="' . esc_url(WXQQ_JUMP_PLUGIN_URL . 'assets/clipboard.min.js') . '"></script>
        <a data-clipboard-text="' . esc_url($siteurl) . '" class="app-download-btn">' . __('点此复制本站网址', 'wxqq-jump') . '</a>
        <script src="' . esc_url(WXQQ_JUMP_PLUGIN_URL . 'assets/layer/layer.js') . '"></script>
        <script type="text/javascript">new ClipboardJS(".app-download-btn");</script>
        <script>
        jQuery(".app-download-btn").click(function() {
            layer.msg("' . __('复制成功，浏览器打开', 'wxqq-jump') . '", function(){
                //关闭后的操作
            });
        });
        </script>
        </body>
        </html>';
        exit;
    }
}
// 提高优先级到最高(1)，确保在其他插件之前执行
add_action('template_redirect', 'wxqq_jump_check', 1);

// 加载语言文件
function wxqq_jump_load_textdomain() {
    load_plugin_textdomain('wxqq-jump', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'wxqq_jump_load_textdomain'); 