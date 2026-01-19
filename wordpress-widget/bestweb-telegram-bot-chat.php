<?php
/**
 * Plugin Name: BestWeb Telegram Bot Chat Widget
 * Plugin URI: https://best-web-studio.ru/
 * Description: Встроенный чат-виджет для интеграции Telegram-бота BestWeb Studio на сайт. Полноценный чат без перехода в Telegram.
 * Version: 2.0.0
 * Author: BestWeb Studio
 * Author URI: https://best-web-studio.ru/
 * Text Domain: bestweb-telegram-bot-chat
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('BESTWEB_TG_BOT_CHAT_VERSION', '2.0.0');
define('BESTWEB_TG_BOT_CHAT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BESTWEB_TG_BOT_CHAT_PLUGIN_URL', plugin_dir_url(__FILE__));

class BestWeb_Telegram_Bot_Chat {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('plugins_loaded', [$this, 'load_textdomain']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_footer', [$this, 'render_widget']);
    }
    
    public function load_textdomain() {
        load_plugin_textdomain('bestweb-telegram-bot-chat', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    public function add_admin_menu() {
        add_options_page(
            __('Telegram Bot Chat Settings', 'bestweb-telegram-bot-chat'),
            __('Telegram Bot Chat', 'bestweb-telegram-bot-chat'),
            'manage_options',
            'bestweb-telegram-bot-chat',
            [$this, 'render_admin_page']
        );
    }
    
    public function register_settings() {
        register_setting('bestweb_tg_bot_chat_settings', 'bestweb_tg_bot_chat_api_url');
        register_setting('bestweb_tg_bot_chat_settings', 'bestweb_tg_bot_chat_enabled');
        register_setting('bestweb_tg_bot_chat_settings', 'bestweb_tg_bot_chat_position');
        register_setting('bestweb_tg_bot_chat_settings', 'bestweb_tg_bot_chat_title_ru');
        register_setting('bestweb_tg_bot_chat_settings', 'bestweb_tg_bot_chat_title_en');
        register_setting('bestweb_tg_bot_chat_settings', 'bestweb_tg_bot_chat_title_th');
    }
    
    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        if (isset($_POST['submit'])) {
            check_admin_referer('bestweb_tg_bot_chat_settings');
            
            update_option('bestweb_tg_bot_chat_api_url', esc_url_raw($_POST['bestweb_tg_bot_chat_api_url']));
            update_option('bestweb_tg_bot_chat_enabled', isset($_POST['bestweb_tg_bot_chat_enabled']) ? 1 : 0);
            update_option('bestweb_tg_bot_chat_position', sanitize_text_field($_POST['bestweb_tg_bot_chat_position']));
            update_option('bestweb_tg_bot_chat_title_ru', sanitize_text_field($_POST['bestweb_tg_bot_chat_title_ru']));
            update_option('bestweb_tg_bot_chat_title_en', sanitize_text_field($_POST['bestweb_tg_bot_chat_title_en']));
            update_option('bestweb_tg_bot_chat_title_th', sanitize_text_field($_POST['bestweb_tg_bot_chat_title_th']));
            
            echo '<div class="notice notice-success"><p>' . __('Settings saved!', 'bestweb-telegram-bot-chat') . '</p></div>';
        }
        
        $apiUrl = get_option('bestweb_tg_bot_chat_api_url', 'https://botsale.1tlt.ru/api/chat.php');
        $enabled = get_option('bestweb_tg_bot_chat_enabled', 1);
        $position = get_option('bestweb_tg_bot_chat_position', 'bottom-right');
        $title_ru = get_option('bestweb_tg_bot_chat_title_ru', 'Чат с менеджером');
        $title_en = get_option('bestweb_tg_bot_chat_title_en', 'Chat with manager');
        $title_th = get_option('bestweb_tg_bot_chat_title_th', 'แชทกับผู้จัดการ');
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('bestweb_tg_bot_chat_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="bestweb_tg_bot_chat_api_url"><?php _e('API URL', 'bestweb-telegram-bot-chat'); ?></label>
                        </th>
                        <td>
                            <input type="url" 
                                   id="bestweb_tg_bot_chat_api_url" 
                                   name="bestweb_tg_bot_chat_api_url" 
                                   value="<?php echo esc_attr($apiUrl); ?>" 
                                   class="regular-text"
                                   placeholder="https://botsale.1tlt.ru/api/chat.php">
                            <p class="description">
                                <?php _e('URL API endpoint для чата (обычно: https://botsale.1tlt.ru/api/chat.php)', 'bestweb-telegram-bot-chat'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="bestweb_tg_bot_chat_enabled"><?php _e('Enable Chat Widget', 'bestweb-telegram-bot-chat'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" 
                                   id="bestweb_tg_bot_chat_enabled" 
                                   name="bestweb_tg_bot_chat_enabled" 
                                   value="1" 
                                   <?php checked($enabled, 1); ?>>
                            <label for="bestweb_tg_bot_chat_enabled">
                                <?php _e('Show chat widget on website', 'bestweb-telegram-bot-chat'); ?>
                            </label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="bestweb_tg_bot_chat_position"><?php _e('Widget Position', 'bestweb-telegram-bot-chat'); ?></label>
                        </th>
                        <td>
                            <select id="bestweb_tg_bot_chat_position" name="bestweb_tg_bot_chat_position">
                                <option value="bottom-right" <?php selected($position, 'bottom-right'); ?>>
                                    <?php _e('Bottom Right', 'bestweb-telegram-bot-chat'); ?>
                                </option>
                                <option value="bottom-left" <?php selected($position, 'bottom-left'); ?>>
                                    <?php _e('Bottom Left', 'bestweb-telegram-bot-chat'); ?>
                                </option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="bestweb_tg_bot_chat_title_ru"><?php _e('Chat Title (Russian)', 'bestweb-telegram-bot-chat'); ?></label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="bestweb_tg_bot_chat_title_ru" 
                                   name="bestweb_tg_bot_chat_title_ru" 
                                   value="<?php echo esc_attr($title_ru); ?>" 
                                   class="regular-text">
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="bestweb_tg_bot_chat_title_en"><?php _e('Chat Title (English)', 'bestweb-telegram-bot-chat'); ?></label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="bestweb_tg_bot_chat_title_en" 
                                   name="bestweb_tg_bot_chat_title_en" 
                                   value="<?php echo esc_attr($title_en); ?>" 
                                   class="regular-text">
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="bestweb_tg_bot_chat_title_th"><?php _e('Chat Title (Thai)', 'bestweb-telegram-bot-chat'); ?></label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="bestweb_tg_bot_chat_title_th" 
                                   name="bestweb_tg_bot_chat_title_th" 
                                   value="<?php echo esc_attr($title_th); ?>" 
                                   class="regular-text">
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    public function enqueue_scripts() {
        if (!$this->is_widget_enabled()) {
            return;
        }
        
        wp_enqueue_style(
            'bestweb-tg-bot-chat-style',
            BESTWEB_TG_BOT_CHAT_PLUGIN_URL . 'assets/chat-style.css',
            [],
            BESTWEB_TG_BOT_CHAT_VERSION
        );
        
        wp_enqueue_script(
            'bestweb-tg-bot-chat-script',
            BESTWEB_TG_BOT_CHAT_PLUGIN_URL . 'assets/chat-script.js',
            [],
            BESTWEB_TG_BOT_CHAT_VERSION,
            true
        );
        
        wp_localize_script('bestweb-tg-bot-chat-script', 'bestwebTgBotChat', [
            'apiUrl' => get_option('bestweb_tg_bot_chat_api_url', 'https://botsale.1tlt.ru/api/chat.php'),
            'position' => get_option('bestweb_tg_bot_chat_position', 'bottom-right'),
            'title' => $this->get_chat_title(),
            'lang' => $this->get_current_language(),
        ]);
    }
    
    public function render_widget() {
        if (!$this->is_widget_enabled()) {
            return;
        }
        
        $position = get_option('bestweb_tg_bot_chat_position', 'bottom-right');
        $title = $this->get_chat_title();
        
        ?>
        <div id="bestweb-tg-bot-chat-widget" class="bestweb-tg-bot-chat-widget bestweb-tg-bot-chat-<?php echo esc_attr($position); ?>">
            <div class="bestweb-tg-bot-chat-button" id="bestweb-tg-bot-chat-button">
                <svg class="bestweb-tg-bot-chat-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.13-.31-1.09-.66.02-.18.27-.37.74-.56 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z" fill="currentColor"/>
                </svg>
            </div>
            
            <div class="bestweb-tg-bot-chat-window" id="bestweb-tg-bot-chat-window" style="display: none;">
                <div class="bestweb-tg-bot-chat-header">
                    <div class="bestweb-tg-bot-chat-header-title">
                        <strong><?php echo esc_html($title); ?></strong>
                    </div>
                    <button class="bestweb-tg-bot-chat-close" id="bestweb-tg-bot-chat-close">×</button>
                </div>
                <div class="bestweb-tg-bot-chat-messages" id="bestweb-tg-bot-chat-messages"></div>
                <div class="bestweb-tg-bot-chat-input-container">
                    <input type="text" 
                           id="bestweb-tg-bot-chat-input" 
                           class="bestweb-tg-bot-chat-input" 
                           placeholder="<?php echo esc_attr($this->get_input_placeholder()); ?>"
                           autocomplete="off">
                    <button id="bestweb-tg-bot-chat-send" class="bestweb-tg-bot-chat-send">→</button>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function is_widget_enabled() {
        return (bool) get_option('bestweb_tg_bot_chat_enabled', 1);
    }
    
    private function get_chat_title() {
        $lang = $this->get_current_language();
        $titles = [
            'ru' => get_option('bestweb_tg_bot_chat_title_ru', 'Чат с менеджером'),
            'en' => get_option('bestweb_tg_bot_chat_title_en', 'Chat with manager'),
            'th' => get_option('bestweb_tg_bot_chat_title_th', 'แชทกับผู้จัดการ'),
        ];
        return $titles[$lang] ?? $titles['en'];
    }
    
    private function get_input_placeholder() {
        $lang = $this->get_current_language();
        $placeholders = [
            'ru' => 'Введите сообщение...',
            'en' => 'Type a message...',
            'th' => 'พิมพ์ข้อความ...',
        ];
        return $placeholders[$lang] ?? $placeholders['en'];
    }
    
    private function get_current_language() {
        if (function_exists('pll_current_language')) {
            $lang = pll_current_language();
            if ($lang) return $lang;
        }
        if (function_exists('icl_get_languages')) {
            $lang = apply_filters('wpml_current_language', null);
            if ($lang) return $lang;
        }
        $lang = isset($_GET['lang']) ? sanitize_text_field($_GET['lang']) : '';
        if (in_array($lang, ['ru', 'en', 'th'])) {
            return $lang;
        }
        return 'en';
    }
}

BestWeb_Telegram_Bot_Chat::get_instance();
