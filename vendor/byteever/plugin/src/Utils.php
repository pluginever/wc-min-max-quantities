<?php

namespace WooCommerceMinMaxQuantities\B8\Plugin;

defined('ABSPATH') || exit;
/**
 * Comprehensive utility class with helper methods.
 *
 * Provides various utility functions organized by category including string manipulation,
 * array operations, file system, number formatting, plugin management, and REST API utilities.
 *
 * @since 1.0.0
 * @package \B8\Plugin
 */
class Utils
{
    // ===================================================================
    // STRING UTILITIES
    // ===================================================================
    /**
     * Limit string to given length.
     *
     * @since 1.0.0
     * @param string $text The string to limit.
     * @param int    $limit The maximum length.
     * @param string $end The ending to append.
     * @return string
     */
    public function str_limit($text, $limit = 100, $end = '...'): string
    {
        if (mb_strlen($text) <= $limit) {
            return $text;
        }
        return mb_substr($text, 0, $limit) . $end;
    }
    /**
     * Generate random string.
     *
     * @since 1.0.0
     * @param int $length The length of random string.
     * @return string
     */
    public function str_random($length = 16): string
    {
        return wp_generate_password($length, false);
    }
    /**
     * Join multiple strings into a normalized string with a custom separator.
     *
     * @since 1.0.0
     * @param array|string $parts The array of strings to normalize and join.
     * @param string       $separator The separator to use between parts.
     * @return string The normalized and joined string.
     */
    public function str_join($parts, $separator = '_'): string
    {
        $cleaned = array();
        foreach (wp_parse_list($parts) as $part) {
            $part = preg_replace('/[^A-Za-z0-9]/', $separator, $part);
            $part = preg_replace('/[' . preg_quote($separator, '/') . ']+/', $separator, $part);
            $part = trim($part, $separator);
            $cleaned[] = strtolower($part);
        }
        return implode($separator, array_filter($cleaned));
    }
    /**
     * Singularize a string.
     *
     * @since 1.0.0
     * @param string $subject The string to singularize.
     * @return string
     */
    public function str_singularize($subject): string
    {
        return preg_replace(array('/ies$/', '/ves$/', '/(?!s)es$/', '/s$/'), array('y', 'f', '', ''), $subject);
    }
    /**
     * Pluralize a string.
     *
     * @since 1.0.0
     * @param string $subject The string to pluralize.
     * @return string
     */
    public function str_pluralize($subject): string
    {
        return preg_replace(array('/y$/', '/f$/', '/fe$/', '/o$/', '/$/'), array('ies', 'ves', 'ves', 'oes', 's'), $this->str_singularize($subject));
    }
    // ===================================================================
    // URL/PATH UTILITIES
    // ===================================================================
    /**
     * Join path segments into a complete path.
     *
     * Takes a base path and additional segments, properly joining them
     * with forward slashes while handling existing slashes correctly.
     *
     * @since 1.0.0
     * @param string       $base Base path or URL.
     * @param array|string $segments Path segments to join.
     * @return string The joined path.
     */
    public function path_join($base, $segments = array()): string
    {
        $path = rtrim($base, '/');
        foreach (wp_parse_list($segments) as $segment) {
            $path .= '/' . ltrim($segment, '/');
        }
        return $path;
    }
    // ===================================================================
    // ARRAY UTILITIES
    // ===================================================================
    /**
     * Get value from array using dot notation.
     *
     * @since 1.0.0
     * @param array  $data Array to search in.
     * @param string $key Key to search for (dot notation supported).
     * @param mixed  $fallback Default value if key not found.
     * @return mixed
     */
    public function arr_get($data, $key, $fallback = null)
    {
        if (array_key_exists($key, $data)) {
            return $data[$key];
        }
        foreach (explode('.', $key) as $segment) {
            if (!is_array($data) || !array_key_exists($segment, $data)) {
                return $fallback;
            }
            $data = $data[$segment];
        }
        return $data;
    }
    /**
     * Set value in array using dot notation.
     *
     * @since 1.0.0
     * @param array  $data Array to modify.
     * @param string $key Key to set (dot notation supported).
     * @param mixed  $value Value to set.
     * @return array
     */
    public function arr_set($data, $key, $value): array
    {
        $keys = explode('.', $key);
        $current =& $data;
        foreach ($keys as $data_key) {
            if (!isset($current[$data_key]) || !is_array($current[$data_key])) {
                $current[$data_key] = array();
            }
            $current =& $current[$data_key];
        }
        $current = $value;
        return $data;
    }
    /**
     * Check if array has key using dot notation.
     *
     * @since 1.0.0
     * @param array  $data Array to check.
     * @param string $key Key to check for.
     * @return bool
     */
    public function arr_has($data, $key): bool
    {
        if (array_key_exists($key, $data)) {
            return true;
        }
        foreach (explode('.', $key) as $segment) {
            if (!is_array($data) || !array_key_exists($segment, $data)) {
                return false;
            }
            $data = $data[$segment];
        }
        return true;
    }
    /**
     * Flatten array to single level.
     *
     * @since 1.0.0
     * @param array  $data Array to flatten.
     * @param string $prefix Prefix for keys.
     * @return array
     */
    public function arr_flatten($data, $prefix = ''): array
    {
        $result = array();
        foreach ($data as $key => $value) {
            $new_key = $prefix ? $prefix . '.' . $key : $key;
            if (is_array($value)) {
                $result = array_merge($result, $this->arr_flatten($value, $new_key));
            } else {
                $result[$new_key] = $value;
            }
        }
        return $result;
    }
    /**
     * Get only specified keys from array.
     *
     * @since 1.0.0
     * @param array $data Source array.
     * @param array $keys Keys to extract.
     * @return array
     */
    public function arr_only($data, $keys): array
    {
        return array_intersect_key($data, array_flip($keys));
    }
    /**
     * Get array without specified keys.
     *
     * @since 1.0.0
     * @param array $data Source array.
     * @param array $keys Keys to exclude.
     * @return array
     */
    public function arr_except($data, $keys): array
    {
        return array_diff_key($data, array_flip($keys));
    }
    // ===================================================================
    // NUMBER UTILITIES
    // ===================================================================
    /**
     * Round a number using the built-in `round` function.
     *
     * @since 1.0.0
     * @param mixed $val The value to round.
     * @param int   $precision The optional number of decimal digits to round to.
     * @param int   $mode A constant to specify the mode in which rounding occurs.
     * @return float The value rounded to the given precision as a float.
     */
    public function num_round($val, $precision = 0, $mode = PHP_ROUND_HALF_UP): float
    {
        if (!is_numeric($val)) {
            $val = 0.0;
        }
        return round((float) $val, $precision, $mode);
    }
    /**
     * Format number with proper thousand separators.
     *
     * @since 1.0.0
     * @param float|string $number Number to format.
     * @param int          $decimals Number of decimal places.
     * @return string Formatted number.
     */
    public function num_format($number, $decimals = 0): string
    {
        return number_format((float) $number, $decimals);
    }
    /**
     * Format currency amount with proper formatting.
     *
     * @since 1.0.0
     * @param float|string $amount Amount to format.
     * @param string       $currency Currency symbol.
     * @param int          $decimals Number of decimal places.
     * @return string Formatted currency.
     */
    public function num_currency($amount, $currency = '$', $decimals = 2): string
    {
        $amount = (float) $amount;
        return $currency . number_format($amount, $decimals);
    }
    /**
     * Format percentage with symbol.
     *
     * @since 1.0.0
     * @param float|string $number Number to format as percentage.
     * @param int          $decimals Number of decimal places.
     * @return string Formatted percentage.
     */
    public function num_percentage($number, $decimals = 1): string
    {
        return number_format((float) $number, $decimals) . '%';
    }
    // ===================================================================
    // DATE UTILITIES
    // ===================================================================
    /**
     * Check if a string represents a valid date in a given format.
     *
     * @param mixed  $date The date string to check.
     * @param string $format The format to verify the date string against.
     * @return bool True if $date represents a valid date/time according to $format, false otherwise.
     */
    public function date_validate($date, $format = 'Y-m-d H:i:s'): bool
    {
        // If already a date object, consider it valid.
        if ($date instanceof \DateTime) {
            return true;
        }
        // if numeric, assume it is a timestamp and valid.
        if (is_numeric($date)) {
            return true;
        }
        // if not a string, it is not valid.
        if (!is_string($date)) {
            return false;
        }
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }
    /**
     * Parse the given attribute to a date.
     *
     * @since 1.0.0
     *
     * @param mixed $value The value to cast.
     *
     * @return int|null The cast timestamp.
     */
    public function date_parse($value): ?int
    {
        if (empty($value)) {
            return null;
        }
        // if value is datetime object, return timestamp.
        if ($value instanceof \DateTime) {
            return $value->getTimestamp();
        }
        // if numeric, assume it is a timestamp already.
        if (is_numeric($value)) {
            return (int) $value;
        }
        $datetime = date_parse($value);
        // if it contains any errors or warnings, return null.
        if (!empty($datetime['error_count']) || !empty($datetime['warning_count'])) {
            return null;
        }
        // if it is not a valid date, return null.
        if (!checkdate($datetime['month'], $datetime['day'], $datetime['year'])) {
            return null;
        }
        // if it is not a valid time, return null.
        if (!empty($datetime['hour']) && !empty($datetime['minute']) && !empty($datetime['second'])) {
            if ($datetime['hour'] < 0 || $datetime['hour'] >= 24 || $datetime['minute'] < 0 || $datetime['minute'] >= 60 || $datetime['second'] < 0 || $datetime['second'] >= 60) {
                return null;
            }
        }
        return strtotime($value);
    }
    // ===================================================================
    // PLUGIN UTILITIES
    // ===================================================================
    /**
     * Check if a plugin is installed.
     *
     * @since 1.0.0
     * @param string $plugin The plugin slug or basename.
     * @return bool
     */
    public function plugin_exists($plugin): bool
    {
        if (!str_contains($plugin, '/')) {
            $plugin = $plugin . '/' . $plugin . '.php';
        }
        $plugins = get_plugins();
        return array_key_exists($plugin, $plugins);
    }
    /**
     * Check if a plugin is active.
     *
     * @since 1.0.0
     * @param string $plugin The plugin slug or basename.
     * @return bool
     */
    public function plugin_active($plugin): bool
    {
        if (!str_contains($plugin, '/')) {
            $plugin = $plugin . '/' . $plugin . '.php';
        }
        $active_plugins = (array) get_option('active_plugins', array());
        if (is_multisite()) {
            $active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
        }
        return in_array($plugin, $active_plugins, true) || array_key_exists($plugin, $active_plugins);
    }
    /**
     * Get plugin install URL.
     *
     * @since 1.0.0
     * @param string $plugin The plugin slug or basename.
     * @return string
     */
    public function plugin_install_link($plugin): string
    {
        if (str_contains($plugin, '/')) {
            $plugin = explode('/', $plugin);
            $plugin = $plugin[0];
        }
        return wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $plugin), 'install-plugin_' . $plugin);
    }
    /**
     * Get plugin activate URL.
     *
     * @since 1.0.0
     * @param string $plugin The plugin slug or basename.
     * @return string
     */
    public function plugin_activate_link($plugin): string
    {
        if (!str_contains($plugin, '/')) {
            $plugin = $plugin . '/' . $plugin . '.php';
        }
        return wp_nonce_url(self_admin_url('plugins.php?action=activate&plugin=' . $plugin), 'activate-plugin_' . $plugin);
    }
    // ===================================================================
    // REST API UTILITIES
    // ===================================================================
    /**
     * Make a request to a REST API endpoint.
     *
     * @since 1.0.0
     * @param string $endpoint Endpoint.
     * @param array  $params Params to pass with request.
     * @param string $method Request method.
     * @return mixed Response data or false on failure.
     */
    public function rest_request($endpoint, $params = array(), $method = 'GET')
    {
        $request = new \WP_REST_Request($method, $endpoint);
        if ($params && 'GET' === $method) {
            $request->set_query_params($params);
        } elseif ($params && in_array($method, array('POST', 'PUT', 'PATCH', 'DELETE'), true)) {
            $request->set_body_params($params);
        }
        $response = rest_do_request($request);
        $server = rest_get_server();
        $json = wp_json_encode($server->response_to_data($response, false));
        return json_decode($json, true);
    }
    // ===================================================================
    // FILE SYSTEM UTILITIES
    // ===================================================================
    /**
     * Get the WordPress filesystem instance.
     *
     * @since 1.0.0
     * @return \WP_Filesystem_Base WordPress filesystem instance.
     * @throws \Exception If the filesystem fails to initialize.
     */
    public function fs(): \WP_Filesystem_Base
    {
        global $wp_filesystem;
        if (!$wp_filesystem instanceof \WP_Filesystem_Base) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            $initialized = WP_Filesystem();
            if (false === $initialized) {
                throw new \Exception('The WordPress filesystem could not be initialized.');
            }
        }
        return $wp_filesystem;
    }
    /**
     * Check if a file exists.
     *
     * @since 1.0.0
     * @param string $file The file path to check.
     * @return bool True if the file exists, false otherwise.
     */
    public function file_exists($file): bool
    {
        $file = $this->file_sanitize($file);
        if (!$this->fs_is_direct()) {
            return file_exists($file);
        }
        return $this->fs()->exists($file);
    }
    /**
     * Get the size of a file in bytes.
     *
     * @since 1.0.0
     * @param string $file The file path to get size of.
     * @return int|false The file size in bytes on success, false on failure.
     */
    public function file_size($file)
    {
        $file = $this->file_sanitize($file);
        return $this->fs()->size($file);
    }
    /**
     * Get the contents of a file as a string.
     *
     * @since 1.0.0
     * @param string $file The file path to read.
     * @return string|false The file contents on success, false on failure.
     */
    public function file_get_contents($file)
    {
        $file = $this->file_sanitize($file);
        return $this->fs()->get_contents($file);
    }
    /**
     * Write contents to a file.
     *
     * @since 1.0.0
     * @param string $file The file path to write to.
     * @param string $contents The contents to write to the file.
     * @return bool True on success, false on failure.
     */
    public function file_put_contents($file, $contents): bool
    {
        $file = $this->file_sanitize($file);
        return $this->fs()->put_contents($file, $contents);
    }
    /**
     * Get the contents of a file as an array of lines.
     *
     * @since 1.0.0
     * @param string $file The file path to read.
     * @return array|false The file contents as an array of lines on success, false on failure.
     */
    public function file_get_array($file)
    {
        $file = $this->file_sanitize($file);
        if (!$this->fs_is_direct()) {
            return file($file);
        }
        return $this->fs()->get_contents_array($file);
    }
    /**
     * Get the last modified time of a file.
     *
     * @since 1.0.0
     * @param string $file The file path to check.
     * @return int|false The last modified time as Unix timestamp on success, false on failure.
     */
    public function file_mtime($file)
    {
        $file = $this->file_sanitize($file);
        return $this->fs()->mtime($file);
    }
    /**
     * Delete a file.
     *
     * @since 1.0.0
     * @param string $file The file path to delete.
     * @return bool True on success, false on failure.
     */
    public function file_delete($file): bool
    {
        $file = $this->file_sanitize($file);
        return $this->fs()->delete($file);
    }
    /**
     * Sanitize a file path by removing dangerous protocols.
     *
     * @since 1.0.0
     * @param string $file The file path to sanitize.
     * @return string The sanitized file path.
     */
    public function file_sanitize($file): string
    {
        if (!str_contains($file, '://') && !str_contains($file, rawurlencode('://'))) {
            return $file;
        }
        $protocols = array('phar://', 'php://', 'glob://', 'data://', 'expect://', 'zip://', 'rar://', 'zlib://');
        $protocols = array_merge($protocols, array_map('urlencode', $protocols));
        foreach ($protocols as $protocol) {
            $pattern = '#^' . preg_quote($protocol, '#') . '#i';
            $file = preg_replace($pattern, '', $file);
        }
        return $file;
    }
    /**
     * Check if the filesystem is using direct file access.
     *
     * @since 1.0.0
     * @return bool True if using direct filesystem access, false otherwise.
     */
    public function fs_is_direct(): bool
    {
        return $this->fs() instanceof \WP_Filesystem_Direct;
    }
    // ===================================================================
    // MISC UTILITIES
    // ===================================================================
    /**
     * Get client IP address with proxy detection.
     *
     * @since 1.0.0
     *
     * @param bool $anonymize Whether to anonymize the IP address (GDPR compliance).
     *
     * @return string Client IP address.
     */
    public function ip_address(bool $anonymize = false): string
    {
        $ip_headers = array('HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
        foreach ($ip_headers as $header) {
            $ip = isset($_SERVER[$header]) ? sanitize_text_field(wp_unslash($_SERVER[$header])) : '';
            if (!empty($ip)) {
                if (str_contains($ip, ',')) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $anonymize ? wp_privacy_anonymize_ip($ip) : $ip;
                }
            }
        }
        $fallback_ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '127.0.0.1';
        return $anonymize ? wp_privacy_anonymize_ip($fallback_ip) : $fallback_ip;
    }
    /**
     * Stream a file download to the browser with security checks.
     *
     * @since 1.0.0
     *
     * @param string      $file     Path to the file.
     * @param string|null $filename Optional download filename.
     *
     * @return bool|\WP_Error True on success, WP_Error on failure.
     */
    public function stream_download(string $file, ?string $filename = null)
    {
        if (!$this->file_exists($file)) {
            return new \WP_Error('file_not_found', 'File not found.');
        }
        $real_path = realpath($file);
        $upload_dir = wp_upload_dir();
        $upload_path = realpath($upload_dir['basedir']);
        if (!$real_path || !$upload_path || !str_starts_with($real_path, $upload_path)) {
            return new \WP_Error('access_denied', 'Access denied.');
        }
        $filename = sanitize_file_name($filename ?? basename($file));
        $file_info = wp_check_filetype($file);
        $mime_type = !empty($file_info['type']) ? $file_info['type'] : 'application/octet-stream';
        $file_size = $this->file_size($file);
        if (function_exists('gc_enable')) {
            gc_enable();
        }
        // phpcs:disable WordPress.PHP.IniSet.Risky, WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_apache_setenv -- Required for file streaming.
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', '1');
        }
        @ini_set('zlib.output_compression', 'Off');
        @ini_set('output_buffering', 'Off');
        @ini_set('output_handler', '');
        // phpcs:enable WordPress.PHP.IniSet.Risky, WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.PHP.DiscouragedPHPFunctions.runtime_configuration_apache_setenv
        ignore_user_abort(true);
        set_time_limit(0);
        nocache_headers();
        header('Content-Type: ' . $mime_type . '; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . $file_size);
        $contents = $this->file_get_contents($file);
        if (false !== $contents) {
            file_put_contents('php://output', $contents);
        }
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        return true;
    }
}