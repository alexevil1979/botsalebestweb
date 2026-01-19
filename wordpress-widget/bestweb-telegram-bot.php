<?php
/**
 * Plugin Name: BestWeb Telegram Bot Widget
 * Plugin URI: https://best-web-studio.ru/
 * Description: Виджет для интеграции Telegram-бота BestWeb Studio на сайт. Плавающая кнопка для быстрого доступа к боту.
 * Version: 1.0.0
 * Author: BestWeb Studio
 * Author URI: https://best-web-studio.ru/
 * Text Domain: bestweb-telegram-bot
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Определяем константы плагина
define('BESTWEB_TG_BOT_VERSION', '1.0.0');
define('BESTWEB_TG_BOT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BESTWEB_TG_BOT_PLUGIN_URL', plugin_dir_url(__FILE__));

class BestWeb_Telegram_Bot {
    
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
        
        // Shortcode для вставки кнопки
        add_shortcode('bestweb_telegram_bot', [$this, 'render_button_shortcode']);
    }
    
    /**
     * Загрузка переводов
     */
    public function load_textdomain() {
        load_plugin_textdomain('bestweb-telegram-bot', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Добавление меню в админке
     */
    public function add_admin_menu() {
        add_options_page(
            __('Telegram Bot Settings', 'bestweb-telegram-bot'),
            __('Telegram Bot', 'bestweb-telegram-bot'),
            'manage_options',
            'bestweb-telegram-bot',
            [$this, 'render_admin_page']
        );
    }
    
    /**
     * Регистрация настроек
     */
    public function register_settings() {
        register_setting('bestweb_tg_bot_settings', 'bestweb_tg_bot_username');
        register_setting('bestweb_tg_bot_settings', 'bestweb_tg_bot_enabled');
        register_setting('bestweb_tg_bot_settings', 'bestweb_tg_bot_position');
        register_setting('bestweb_tg_bot_settings', 'bestweb_tg_bot_text_ru');
        register_setting('bestweb_tg_bot_settings', 'bestweb_tg_bot_text_en');
        register_setting('bestweb_tg_bot_settings', 'bestweb_tg_bot_text_th');
    }
    
    /**
     * Страница настроек в админке
     */
    public function render_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        // Сохранение настроек
        if (isset($_POST['submit'])) {
            check_admin_referer('bestweb_tg_bot_settings');
            
            update_option('bestweb_tg_bot_username', sanitize_text_field($_POST['bestweb_tg_bot_username']));
            update_option('bestweb_tg_bot_enabled', isset($_POST['bestweb_tg_bot_enabled']) ? 1 : 0);
            update_option('bestweb_tg_bot_position', sanitize_text_field($_POST['bestweb_tg_bot_position']));
            update_option('bestweb_tg_bot_text_ru', sanitize_text_field($_POST['bestweb_tg_bot_text_ru']));
            update_option('bestweb_tg_bot_text_en', sanitize_text_field($_POST['bestweb_tg_bot_text_en']));
            update_option('bestweb_tg_bot_text_th', sanitize_text_field($_POST['bestweb_tg_bot_text_th']));
            
            echo '<div class="notice notice-success"><p>' . __('Settings saved!', 'bestweb-telegram-bot') . '</p></div>';
        }
        
        $username = get_option('bestweb_tg_bot_username', '');
        $enabled = get_option('bestweb_tg_bot_enabled', 1);
        $position = get_option('bestweb_tg_bot_position', 'bottom-right');
        $text_ru = get_option('bestweb_tg_bot_text_ru', 'Начать чат');
        $text_en = get_option('bestweb_tg_bot_text_en', 'Start chat');
        $text_th = get_option('bestweb_tg_bot_text_th', 'เริ่มแชท');
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            
            <form method="post" action="">
                <?php wp_nonce_field('bestweb_tg_bot_settings'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="bestweb_tg_bot_username"><?php _e('Telegram Bot Username', 'bestweb-telegram-bot'); ?></label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="bestweb_tg_bot_username" 
                                   name="bestweb_tg_bot_username" 
                                   value="<?php echo esc_attr($username); ?>" 
                                   class="regular-text"
                                   placeholder="@YourBotName">
                            <p class="description">
                                <?php _e('Enter bot username without @ (e.g., YourBotName)', 'bestweb-telegram-bot'); ?>
                            </p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="bestweb_tg_bot_enabled"><?php _e('Enable Widget', 'bestweb-telegram-bot'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" 
                                   id="bestweb_tg_bot_enabled" 
                                   name="bestweb_tg_bot_enabled" 
                                   value="1" 
                                   <?php checked($enabled, 1); ?>>
                            <label for="bestweb_tg_bot_enabled">
                                <?php _e('Show floating button on website', 'bestweb-telegram-bot'); ?>
                            </label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="bestweb_tg_bot_position"><?php _e('Button Position', 'bestweb-telegram-bot'); ?></label>
                        </th>
                        <td>
                            <select id="bestweb_tg_bot_position" name="bestweb_tg_bot_position">
                                <option value="bottom-right" <?php selected($position, 'bottom-right'); ?>>
                                    <?php _e('Bottom Right', 'bestweb-telegram-bot'); ?>
                                </option>
                                <option value="bottom-left" <?php selected($position, 'bottom-left'); ?>>
                                    <?php _e('Bottom Left', 'bestweb-telegram-bot'); ?>
                                </option>
                                <option value="top-right" <?php selected($position, 'top-right'); ?>>
                                    <?php _e('Top Right', 'bestweb-telegram-bot'); ?>
                                </option>
                                <option value="top-left" <?php selected($position, 'top-left'); ?>>
                                    <?php _e('Top Left', 'bestweb-telegram-bot'); ?>
                                </option>
                            </select>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="bestweb_tg_bot_text_ru"><?php _e('Button Text (Russian)', 'bestweb-telegram-bot'); ?></label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="bestweb_tg_bot_text_ru" 
                                   name="bestweb_tg_bot_text_ru" 
                                   value="<?php echo esc_attr($text_ru); ?>" 
                                   class="regular-text">
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="bestweb_tg_bot_text_en"><?php _e('Button Text (English)', 'bestweb-telegram-bot'); ?></label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="bestweb_tg_bot_text_en" 
                                   name="bestweb_tg_bot_text_en" 
                                   value="<?php echo esc_attr($text_en); ?>" 
                                   class="regular-text">
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="bestweb_tg_bot_text_th"><?php _e('Button Text (Thai)', 'bestweb-telegram-bot'); ?></label>
                        </th>
                        <td>
                            <input type="text" 
                                   id="bestweb_tg_bot_text_th" 
                                   name="bestweb_tg_bot_text_th" 
                                   value="<?php echo esc_attr($text_th); ?>" 
                                   class="regular-text">
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
            
            <hr>
            
            <h2><?php _e('Shortcode', 'bestweb-telegram-bot'); ?></h2>
            <p><?php _e('You can also insert the button using shortcode:', 'bestweb-telegram-bot'); ?></p>
            <code>[bestweb_telegram_bot]</code>
        </div>
        <?php
    }
    
    /**
     * Подключение стилей и скриптов
     */
    public function enqueue_scripts() {
        if (!$this->is_widget_enabled()) {
            return;
        }
        
        wp_enqueue_style(
            'bestweb-tg-bot-style',
            BESTWEB_TG_BOT_PLUGIN_URL . 'assets/style.css',
            [],
            BESTWEB_TG_BOT_VERSION
        );
        
        wp_enqueue_script(
            'bestweb-tg-bot-script',
            BESTWEB_TG_BOT_PLUGIN_URL . 'assets/script.js',
            [],
            BESTWEB_TG_BOT_VERSION,
            true
        );
        
        // Передаем настройки в JavaScript
        wp_localize_script('bestweb-tg-bot-script', 'bestwebTgBot', [
            'username' => $this->get_bot_username(),
            'position' => get_option('bestweb_tg_bot_position', 'bottom-right'),
            'text' => $this->get_button_text(),
        ]);
    }
    
    /**
     * Рендер виджета в футере
     */
    public function render_widget() {
        if (!$this->is_widget_enabled()) {
            return;
        }
        
        $username = $this->get_bot_username();
        if (empty($username)) {
            return;
        }
        
        $position = get_option('bestweb_tg_bot_position', 'bottom-right');
        $text = $this->get_button_text();
        
        ?>
        <div id="bestweb-tg-bot-widget" class="bestweb-tg-bot-widget bestweb-tg-bot-<?php echo esc_attr($position); ?>">
            <a href="https://t.me/<?php echo esc_attr($username); ?>?start=from_website" 
               target="_blank" 
               rel="noopener noreferrer"
               class="bestweb-tg-bot-button"
               aria-label="<?php echo esc_attr($text); ?>">
                <svg class="bestweb-tg-bot-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.13-.31-1.09-.66.02-.18.27-.37.74-.56 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z" fill="currentColor"/>
                </svg>
                <span class="bestweb-tg-bot-text"><?php echo esc_html($text); ?></span>
            </a>
        </div>
        <?php
    }
    
    /**
     * Shortcode для вставки кнопки
     */
    public function render_button_shortcode($atts) {
        $username = $this->get_bot_username();
        if (empty($username)) {
            return '';
        }
        
        $atts = shortcode_atts([
            'text' => $this->get_button_text(),
            'class' => '',
        ], $atts);
        
        $text = !empty($atts['text']) ? $atts['text'] : $this->get_button_text();
        
        return sprintf(
            '<a href="https://t.me/%s?start=from_website" target="_blank" rel="noopener noreferrer" class="bestweb-tg-bot-button-inline %s">%s</a>',
            esc_attr($username),
            esc_attr($atts['class']),
            esc_html($text)
        );
    }
    
    /**
     * Проверка, включен ли виджет
     */
    private function is_widget_enabled() {
        return (bool) get_option('bestweb_tg_bot_enabled', 1);
    }
    
    /**
     * Получить username бота
     */
    private function get_bot_username() {
        $username = get_option('bestweb_tg_bot_username', '');
        return ltrim($username, '@');
    }
    
    /**
     * Получить текст кнопки в зависимости от языка
     */
    private function get_button_text() {
        // Определяем язык сайта
        $lang = $this->get_current_language();
        
        $texts = [
            'ru' => get_option('bestweb_tg_bot_text_ru', 'Начать чат'),
            'en' => get_option('bestweb_tg_bot_text_en', 'Start chat'),
            'th' => get_option('bestweb_tg_bot_text_th', 'เริ่มแชท'),
        ];
        
        return $texts[$lang] ?? $texts['en'];
    }
    
    /**
     * Определить текущий язык сайта
     */
    private function get_current_language() {
        // Проверяем популярные плагины мультиязычности
        if (function_exists('pll_current_language')) {
            // Polylang
            $lang = pll_current_language();
            if ($lang) {
                return $lang;
            }
        }
        
        if (function_exists('icl_get_languages')) {
            // WPML
            $lang = apply_filters('wpml_current_language', null);
            if ($lang) {
                return $lang;
            }
        }
        
        if (defined('QTX_VERSION')) {
            // qTranslate-X
            $lang = get_query_var('lang');
            if ($lang) {
                return $lang;
            }
        }
        
        // Проверяем параметр URL
        $lang = isset($_GET['lang']) ? sanitize_text_field($_GET['lang']) : '';
        if (in_array($lang, ['ru', 'en', 'th'])) {
            return $lang;
        }
        
        // Проверяем домен/поддомен
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if (strpos($host, '.ru') !== false || strpos($host, '/ru') !== false) {
            return 'ru';
        }
        if (strpos($host, '/th') !== false || strpos($host, '.th') !== false) {
            return 'th';
        }
        
        // По умолчанию английский
        return 'en';
    }
}

// Инициализация плагина
BestWeb_Telegram_Bot::get_instance();
