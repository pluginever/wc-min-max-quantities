<?php

namespace WooCommerceMinMaxQuantities\B8\Plugin\Services;

defined('ABSPATH') || exit;
/**
 * High-performance data sanitization utility for WordPress.
 *
 * Provides fast data cleaning using switch-based rules with pipe-separated
 * rule strings and comprehensive sanitization options.
 *
 * @since 1.0.0
 * @package \B8\Plugin
 */
class Sanitizer
{
    /**
     * Sanitize data using specified rules.
     *
     * @since 1.0.0
     *
     * @param array $data Data to sanitize.
     * @param array $rules Sanitization rules (field => rule_string).
     *
     * @return array Sanitized data.
     */
    public function sanitize($data, $rules): array
    {
        $sanitized = array();
        foreach ($data as $field => $value) {
            $value = $value ?? '';
            if (isset($rules[$field])) {
                $rule_items = explode('|', $rules[$field]);
                foreach ($rule_items as $rule_item) {
                    $rule_item = trim($rule_item);
                    if (empty($rule_item)) {
                        continue;
                    }
                    $parameters = array();
                    if (str_contains($rule_item, ':')) {
                        list($rule_name, $params_string) = explode(':', $rule_item, 2);
                        $parameters = array_map('trim', explode(',', $params_string));
                    } else {
                        $rule_name = $rule_item;
                    }
                    $value = $this->sanitize_value($value, $rule_name, $parameters);
                }
                $sanitized[$field] = $value;
            } else {
                $sanitized[$field] = $this->clean_value($value);
            }
        }
        return $sanitized;
    }
    /**
     * Apply single sanitization rule using high-performance switch.
     *
     * @since 1.0.0
     *
     * @param mixed  $value Value to sanitize.
     * @param string $rule Rule name.
     * @param array  $parameters Rule parameters.
     *
     * @return mixed Sanitized value.
     */
    protected function sanitize_value($value, $rule, $parameters)
    {
        if (is_array($value)) {
            return array_map(fn($item) => $this->sanitize_value($item, $rule, $parameters), $value);
        }
        switch ($rule) {
            case 'textarea':
                $sanitized = sanitize_textarea_field($value);
                break;
            case 'email':
                $sanitized = sanitize_email($value);
                break;
            case 'url':
                $sanitized = esc_url_raw($value);
                break;
            case 'key':
                $sanitized = sanitize_key($value);
                break;
            case 'filename':
                $sanitized = sanitize_file_name($value);
                break;
            case 'slug':
                $sanitized = sanitize_title_with_dashes($value);
                break;
            case 'bool':
            case 'boolean':
                $sanitized = (bool) $value;
                break;
            case 'int':
            case 'integer':
                $sanitized = absint($value);
                break;
            case 'float':
            case 'number':
                $sanitized = (float) $value;
                break;
            case 'strip_tags':
                $sanitized = wp_strip_all_tags($value);
                break;
            case 'trim':
                $sanitized = trim((string) $value);
                break;
            case 'lower':
            case 'lowercase':
                $sanitized = strtolower((string) $value);
                break;
            case 'upper':
            case 'uppercase':
                $sanitized = strtoupper((string) $value);
                break;
            case 'ucfirst':
                $sanitized = ucfirst(strtolower((string) $value));
                break;
            case 'ucwords':
            case 'title_case':
                $sanitized = ucwords(strtolower((string) $value));
                break;
            case 'esc_html':
                $sanitized = esc_html($value);
                break;
            case 'esc_attr':
            case 'attr':
                $sanitized = esc_attr($value);
                break;
            case 'html':
                $sanitized = wp_kses_post($value);
                break;
            case 'json':
                $sanitized = $this->sanitize_json($value);
                break;
            case 'alpha':
                $sanitized = preg_replace('/[^a-zA-Z]/', '', (string) $value);
                break;
            case 'alpha_num':
                $sanitized = preg_replace('/[^a-zA-Z0-9]/', '', (string) $value);
                break;
            case 'alpha_dash':
                $sanitized = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $value);
                break;
            case 'numeric':
                $sanitized = preg_replace('/[^0-9.-]/', '', (string) $value);
                break;
            case 'collapse_ws':
                $sanitized = preg_replace('/\s+/', ' ', trim((string) $value));
                break;
            case 'limit':
                $sanitized = $this->limit_string((string) $value, $parameters);
                break;
            case 'pad':
                $sanitized = $this->pad_string((string) $value, $parameters);
                break;
            case 'text':
            default:
                $sanitized = sanitize_text_field($value);
                break;
        }
        return $sanitized;
    }
    /**
     * Default sanitization for values without specific rules.
     *
     * @since 1.0.0
     *
     * @param mixed $value Value to clean.
     *
     * @return mixed Cleaned value.
     */
    protected function clean_value($value)
    {
        if (is_array($value)) {
            return array_map(array($this, 'clean_value'), $value);
        }
        return is_scalar($value) ? sanitize_text_field($value) : $value;
    }
    /**
     * Sanitize and validate JSON string.
     *
     * @since 1.0.0
     *
     * @param mixed $value JSON string to sanitize.
     *
     * @return mixed Decoded JSON data or null if invalid.
     */
    protected function sanitize_json($value)
    {
        if (!is_string($value)) {
            return null;
        }
        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }
    /**
     * Limit string length with optional ending.
     *
     * @since 1.0.0
     *
     * @param string $value String to limit.
     * @param array  $parameters [max_length, ending].
     *
     * @return string Limited string.
     */
    protected function limit_string($value, $parameters): string
    {
        $limit = isset($parameters[0]) ? (int) $parameters[0] : 100;
        $ending = isset($parameters[1]) ? $parameters[1] : '';
        if (mb_strlen($value) <= $limit) {
            return $value;
        }
        return mb_substr($value, 0, $limit) . $ending;
    }
    /**
     * Pad string to specified length.
     *
     * @since 1.0.0
     *
     * @param string $value String to pad.
     * @param array  $parameters [length, pad_string, pad_type].
     *
     * @return string Padded string.
     */
    protected function pad_string($value, $parameters): string
    {
        $length = isset($parameters[0]) ? (int) $parameters[0] : 0;
        $pad_string = isset($parameters[1]) ? $parameters[1] : ' ';
        $pad_type = isset($parameters[2]) ? $parameters[2] : 'right';
        switch ($pad_type) {
            case 'left':
                $type = STR_PAD_LEFT;
                break;
            case 'both':
                $type = STR_PAD_BOTH;
                break;
            default:
                $type = STR_PAD_RIGHT;
                break;
        }
        return str_pad($value, $length, $pad_string, $type);
    }
}