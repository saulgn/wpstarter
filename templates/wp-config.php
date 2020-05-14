<?php
/**
 * This file is generated by WP Starter package, and contains base configuration of the WordPress.
 *
 * All the configuration constants used by WordPress are set via environment variables.
 * Default settings are provided in this file for most common settings, however database settings
 * are required, you can get them from your web host.
 *
 */
use WeCodeMore\WpStarter\Env\WordPressEnvBridge;

AUTOLOAD: {
    /** Composer autoload. */
    require_once realpath(__DIR__ . '{{{AUTOLOAD_PATH}}}');

    /** A reference to `.env` folder path. */
    define('WPSTARTER_PATH', realpath(__DIR__ . '{{{ENV_REL_PATH}}}'));
} #/AUTOLOAD

ENV_VARIABLES: {
    /**
     * Define all WordPress constants from environment variables.
     * Environment variables will be loaded from file, unless `WPSTARTER_ENV_LOADED` env var is already
     * setup e.g. via webserver configuration.
     * In that case all environment variables are assumed to be set.
     * Environment variables that are set in the *real* environment (e.g. via webserver) will not be
     * overridden from file, even if `WPSTARTER_ENV_LOADED` is not set.
     */
    $envLoader = '{{{CACHE_ENV}}}'
        ? WordPressEnvBridge::buildFromCacheDump(WPSTARTER_PATH . WordPressEnvBridge::CACHE_DUMP_FILE)
        : new WordPressEnvBridge();

    if (!$envLoader->hasCachedValues()) {
        $envLoader->load('{{{ENV_FILE_NAME}}}', WPSTARTER_PATH);
        $envName = $envLoader->read('WP_ENV') ?? $envLoader->read('WORDPRESS_ENV');
        if ($envName && $envName !== 'example') {
            $envLoader->loadAppended("{{{ENV_FILE_NAME}}}.{$envName}", WPSTARTER_PATH);
        }
        $envLoader->setupConstants();
    }

    isset($envName) or $envName = $envLoader->read('WP_ENV') ?? $envLoader->read('WORDPRESS_ENV');

    if (
        $envName
        && file_exists(WPSTARTER_PATH . "/{$envName}.php")
        && is_readable(WPSTARTER_PATH . "/{$envName}.php")
    ) {
        require_once WPSTARTER_PATH . "/{$envName}.php";
    }
} #/ENV_VARIABLES

KEYS: {
    /**#@+
     * Authentication Unique Keys and Salts.
     */
    defined('AUTH_KEY') or define('AUTH_KEY', '{{{AUTH_KEY}}}');
    defined('SECURE_AUTH_KEY') or define('SECURE_AUTH_KEY', '{{{SECURE_AUTH_KEY}}}');
    defined('LOGGED_IN_KEY') or define('LOGGED_IN_KEY', '{{{LOGGED_IN_KEY}}}');
    defined('NONCE_KEY') or define('NONCE_KEY', '{{{NONCE_KEY}}}');
    defined('AUTH_SALT') or define('AUTH_SALT', '{{{AUTH_SALT}}}');
    defined('SECURE_AUTH_SALT') or define('SECURE_AUTH_SALT', '{{{SECURE_AUTH_SALT}}}');
    defined('LOGGED_IN_SALT') or define('LOGGED_IN_SALT', '{{{LOGGED_IN_SALT}}}');
    defined('NONCE_SALT') or define('NONCE_SALT', '{{{NONCE_SALT}}}');
    /**#@-*/
} #/KEYS


DB_SETUP : {
    /** Set optional database settings if not already set. */
    defined('DB_HOST') or define('DB_HOST', 'localhost');
    defined('DB_CHARSET') or define('DB_CHARSET', 'utf8');
    defined('DB_COLLATE') or define('DB_COLLATE', '');

    /**
     * WordPress Database Table prefix.
     */
    global $table_prefix;
    $table_prefix = $envLoader->read('DB_TABLE_PREFIX') ?: 'wp_';
} #/DB_SETUP

/** Absolute path to the WordPress directory. */
defined('ABSPATH') or define('ABSPATH', realpath(__DIR__ . '{{{WP_INSTALL_PATH}}}') . '/');

EARLY_HOOKS : {
    /** Load plugin.php early, so we can call `add_action` below. */
    require_once ABSPATH . 'wp-includes/plugin.php';

    /**
     * Load early hooks file if any.
     * Early hooks file allows to add hooks that are triggered before plugins are loaded, e.g.
     * "enable_loading_advanced_cache_dropin" or to just-in-time define configuration constants.
     */
    if (
        '{{{EARLY_HOOKS_FILE}}}'
        && file_exists(__DIR__ . '{{{EARLY_HOOKS_FILE}}}')
        && is_readable(__DIR__ . '{{{EARLY_HOOKS_FILE}}}')
    ) {
        require_once __DIR__ . '{{{EARLY_HOOKS_FILE}}}';
    }
} #/EARLY_HOOKS

DEFAULT_ENV : {
    /** Environment-aware settings. Be creative, but avoid having sensitive settings here. */
    switch ($envName) {
        case 'local':
        case 'development':
            defined('WP_DEBUG') or define('WP_DEBUG', true);
            defined('WP_DEBUG_DISPLAY') or define('WP_DEBUG_DISPLAY', true);
            defined('WP_DEBUG_LOG') or define('WP_DEBUG_LOG', false);
            defined('SAVEQUERIES') or define('SAVEQUERIES', true);
            defined('SCRIPT_DEBUG') or define('SCRIPT_DEBUG', true);
            defined('WP_DISABLE_FATAL_ERROR_HANDLER') or define('WP_DISABLE_FATAL_ERROR_HANDLER',
                true);
            break;
        case 'staging':
            defined('WP_DEBUG') or define('WP_DEBUG', true);
            defined('WP_DEBUG_DISPLAY') or define('WP_DEBUG_DISPLAY', false);
            defined('WP_DEBUG_LOG') or define('WP_DEBUG_LOG', true);
            defined('SAVEQUERIES') or define('SAVEQUERIES', false);
            defined('SCRIPT_DEBUG') or define('SCRIPT_DEBUG', true);
            break;
        case 'production':
        default:
            defined('WP_DEBUG') or define('WP_DEBUG', false);
            defined('WP_DEBUG_DISPLAY') or define('WP_DEBUG_DISPLAY', false);
            defined('WP_DEBUG_LOG') or define('WP_DEBUG_LOG', false);
            defined('SAVEQUERIES') or define('SAVEQUERIES', false);
            defined('SCRIPT_DEBUG') or define('SCRIPT_DEBUG', false);
            break;
    }
    if ($envName === 'local' && !defined('WP_LOCAL_DEV')) {
        define('WP_LOCAL_DEV', true);
    }
} #/DEFAULT_ENV

SSL_FIX : {
    if (
        $envLoader->read('WP_FORCE_SSL_FORWARDED_PROTO')
        && array_key_exists('HTTP_X_FORWARDED_PROTO', $_SERVER)
        && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https'
    ) {
        $_SERVER['HTTPS'] = 'on';
    }
} #/SSL_FIX

URL_CONSTANTS : {
    if (!defined('WP_HOME')) {
        $home = filter_var($_SERVER['HTTPS'] ?? '', FILTER_VALIDATE_BOOLEAN) ? 'https://' : 'http://';
        $home .= $_SERVER['SERVER_NAME'] ?? 'localhost';
        define('WP_HOME', $home);
        unset($home);
    }

    /** Set WordPress other URL / path constants not set via environment variables. */
    defined('WP_SITEURL') or define('WP_SITEURL', rtrim(WP_HOME, '/') . '/{{{WP_SITEURL_RELATIVE}}}');
    defined('WP_CONTENT_DIR') or define('WP_CONTENT_DIR', realpath(__DIR__ . '{{{WP_CONTENT_PATH}}}'));
    defined('WP_CONTENT_URL') or define('WP_CONTENT_URL', rtrim(WP_HOME, '/') . '/{{{WP_CONTENT_URL_RELATIVE}}}');
} #/URL_CONSTANTS

THEMES_REGISTER : {
    /** Register default themes inside WordPress package wp-content folder. */
    if (filter_var('{{{REGISTER_THEME_DIR}}}', FILTER_VALIDATE_BOOLEAN)) {
        add_action(
            'plugins_loaded',
            static function () {
                register_theme_directory(ABSPATH . 'wp-content/themes');
            },
            0
        );
    }
} #/THEMES_REGISTER

ADMIN_COLOR : {
    /** Allow changing admin color scheme. Useful to distinguish environments in the dashboard. */
    add_filter(
        'get_user_option_admin_color',
        static function ($color) use ($envLoader) {
            return $envLoader->read('WP_ADMIN_COLOR') ?: $color;
        },
        999
    );
} #/ADMIN_COLOR

ENV_CACHE : {
    /** On shutdown we dump environment so that on subsequent requests we can load it faster */
    if ('{{{CACHE_ENV}}}' && $envLoader->isWpSetup()) {
        register_shutdown_function(
            static function () use ($envLoader, $envName) {
                $skipCache = apply_filters('wpstarter.skip-cache-env', $envName === 'local',
                    $envName);
                $skipCache or $envLoader->dumpCached(WPSTARTER_PATH . WordPressEnvBridge::CACHE_DUMP_FILE);
            }
        );
    }
} #/ADMIN_COLOR

CLEAN_UP : {
    unset($envName, $envLoader, $cacheEnv);
} #/CLEAN_UP

###################################################################################################
#  I've seen things you people wouldn't believe. Attack ships on fire off the shoulder of Orion.  #
#                 I watched C-beams glitter in the dark near the Tannhauser gate.                 #
#            All those moments will be lost in time, like tears in rain. Time to die.             #
###################################################################################################

/* That's all, stop editing! Happy blogging. */

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
