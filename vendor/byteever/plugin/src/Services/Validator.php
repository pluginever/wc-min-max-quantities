<?php

namespace WooCommerceMinMaxQuantities\B8\Plugin\Services;

use WooCommerceMinMaxQuantities\B8\Plugin\Utils;
defined('ABSPATH') || exit;
/**
 * High-performance data validation utility for WordPress.
 *
 * Provides comprehensive validation rules with custom error messages,
 * field labels, and WordPress-specific validators.
 *
 * @since 1.0.0
 * @package \B8\Plugin
 */
class Validator
{
    /**
     * Original input data.
     *
     * @since 1.0.0
     * @var array
     */
    protected $data = array();
    /**
     * Validation errors.
     *
     * @since 1.0.0
     * @var array
     */
    protected $errors = array();
    /**
     * Validated data (after validation passes).
     *
     * @since 1.0.0
     * @var array
     */
    protected $validated_data = array();
    /**
     * Custom validation messages.
     *
     * @since 1.0.0
     * @var array
     */
    protected array $messages = array('required' => '%s is required.', 'email' => '%s must be a valid email address.', 'url' => '%s must be a valid URL.', 'numeric' => '%s must be a valid number.', 'integer' => '%s must be a valid integer.', 'alpha' => '%s may only contain letters.', 'alpha_num' => '%s may only contain letters and numbers.', 'alpha_dash' => '%s may only contain letters, numbers, dashes, and underscores.', 'min' => '%s must be at least %s.', 'max' => '%s must not be greater than %s.', 'between' => '%s must be between %s and %s.', 'in' => '%s is an invalid selection.', 'not_in' => '%s is an invalid selection.', 'accepted' => '%s must be accepted.', 'boolean' => '%s must be true or false.', 'string' => '%s must be a valid text.', 'date' => '%s must be a valid date.', 'date_format' => '%s must be a valid date in the specified format.', 'ip' => '%s must be a valid IP address.', 'unique' => '%s already exists.', 'exists' => '%s does not exist.');
    /**
     * Set custom validation messages.
     *
     * Merges with existing messages, overriding any duplicates.
     *
     * @since 1.0.0
     *
     * @param array $messages Custom messages keyed by rule name.
     *
     * @return self
     */
    public function set_messages(array $messages): self
    {
        $this->messages = array_merge($this->messages, $messages);
        return $this;
    }
    /**
     * Validate data with rules, labels, and messages.
     *
     * @since 1.0.0
     *
     * @param array $data Data to validate.
     * @param array $rules Validation rules.
     * @param array $messages Custom error messages.
     * @param array $labels Custom field labels.
     *
     * @return self Validator instance for method chaining.
     */
    public function validate($data, $rules, $messages = array(), array $labels = array()): self
    {
        $this->data = $data;
        $this->errors = array();
        $this->validated_data = $data;
        foreach ($rules as $field => $field_rules) {
            $value = $data[$field] ?? null;
            $field_rule_list = is_string($field_rules) ? explode('|', $field_rules) : (is_array($field_rules) ? $field_rules : array());
            $is_required = in_array('required', $field_rule_list, true);
            if (!$is_required && $this->is_empty_value($value)) {
                continue;
            }
            foreach ($field_rule_list as $rule) {
                $parameters = array();
                if (str_contains($rule, ':')) {
                    $parts = explode(':', $rule, 2);
                    $rule_name = $parts[0];
                    $params_string = $parts[1];
                    $parameters = array_map('trim', explode(',', $params_string));
                } else {
                    $rule_name = $rule;
                }
                if ('required' !== $rule_name && $this->is_empty_value($value)) {
                    continue;
                }
                if (!$this->validate_value($value, $rule_name, $parameters)) {
                    $this->add_error($field, $rule_name, $parameters, $messages, $labels);
                    if ('required' === $rule_name) {
                        break;
                    }
                }
            }
        }
        return $this;
    }
    /**
     * Apply single validation rule using high-performance switch.
     *
     * @since 1.0.0
     *
     * @param mixed  $value Value to validate.
     * @param string $rule Rule name.
     * @param array  $parameters Rule parameters.
     *
     * @return bool True if validation passes.
     */
    protected function validate_value($value, $rule, $parameters): bool
    {
        switch ($rule) {
            case 'required':
                $result = !$this->is_empty_value($value);
                break;
            case 'email':
                $result = is_email($value);
                break;
            case 'url':
                $result = wp_http_validate_url($value) !== false;
                break;
            case 'numeric':
                $result = is_numeric($value);
                break;
            case 'integer':
                $result = filter_var($value, FILTER_VALIDATE_INT) !== false;
                break;
            case 'alpha':
                $result = ctype_alpha((string) $value);
                break;
            case 'alpha_num':
                $result = ctype_alnum((string) $value);
                break;
            case 'alpha_dash':
                $result = preg_match('/^[a-zA-Z0-9_-]+$/', (string) $value) === 1;
                break;
            case 'min':
                $result = $this->validate_min($value, $parameters);
                break;
            case 'max':
                $result = $this->validate_max($value, $parameters);
                break;
            case 'between':
                $result = isset($parameters[0], $parameters[1]) && $this->validate_min($value, array($parameters[0])) && $this->validate_max($value, array($parameters[1]));
                break;
            case 'in':
                $result = in_array($value, $parameters, true);
                break;
            case 'not_in':
                $result = !in_array($value, $parameters, true);
                break;
            case 'accepted':
                $result = in_array($value, array('yes', 'on', 1, '1', true), true);
                break;
            case 'boolean':
                $result = in_array($value, array(true, false, 0, 1, '0', '1'), true);
                break;
            case 'string':
                $result = is_string($value);
                break;
            case 'date':
                $result = is_string($value) && strtotime($value) !== false;
                break;
            case 'date_format':
                $result = $this->validate_date_format($value, $parameters);
                break;
            case 'ip':
                $result = filter_var($value, FILTER_VALIDATE_IP) !== false;
                break;
            case 'unique':
                $result = $this->validate_unique($value, $parameters);
                break;
            case 'exists':
                $result = $this->validate_exists($value, $parameters);
                break;
            default:
                $result = true;
                break;
        }
        return (bool) $result;
    }
    /**
     * Get validated data if validation passes.
     *
     * @since 1.0.0
     * @return array Validated data if validation passes, empty array if fails.
     */
    public function validated(): array
    {
        return $this->fails() ? array() : $this->validated_data;
    }
    /**
     * Check if validation fails.
     *
     * @since 1.0.0
     * @return bool True if validation fails.
     */
    public function fails(): bool
    {
        return !empty($this->errors);
    }
    /**
     * Check if validation passes.
     *
     * @since 1.0.0
     * @return bool True if validation passes.
     */
    public function passes(): bool
    {
        return empty($this->errors);
    }
    /**
     * Get all validation errors.
     *
     * @since 1.0.0
     * @return array Validation errors grouped by field.
     */
    public function errors(): array
    {
        return $this->errors;
    }
    /**
     * Get first error message (any field).
     *
     * @since 1.0.0
     * @return string|null First error message or null.
     */
    public function first_error(): ?string
    {
        foreach ($this->errors as $field_errors) {
            if (!empty($field_errors)) {
                return $field_errors[0];
            }
        }
        return null;
    }
    /**
     * Check if value is considered empty for validation.
     *
     * @since 1.0.0
     *
     * @param mixed $value Value to check.
     *
     * @return bool True if value is empty.
     */
    protected function is_empty_value($value): bool
    {
        if (is_string($value)) {
            $value = trim($value);
        }
        if (null === $value || '' === $value || is_array($value) && empty($value)) {
            return true;
        }
        return false;
    }
    /**
     * Validate minimum length/value.
     *
     * @since 1.0.0
     *
     * @param mixed $value Value to validate.
     * @param array $parameters Parameters [min_value].
     *
     * @return bool True if valid.
     */
    protected function validate_min($value, $parameters): bool
    {
        if (!isset($parameters[0])) {
            return false;
        }
        $min = (float) $parameters[0];
        if (is_string($value)) {
            return $min <= mb_strlen($value);
        }
        if (is_numeric($value)) {
            return $min <= (float) $value;
        }
        if (is_array($value)) {
            return $min <= count($value);
        }
        return false;
    }
    /**
     * Validate maximum length/value.
     *
     * @since 1.0.0
     *
     * @param mixed $value Value to validate.
     * @param array $parameters Parameters [max_value].
     *
     * @return bool True if valid.
     */
    protected function validate_max($value, $parameters): bool
    {
        if (!isset($parameters[0])) {
            return false;
        }
        $max = (float) $parameters[0];
        if (is_string($value)) {
            return $max >= mb_strlen($value);
        }
        if (is_numeric($value)) {
            return $max >= (float) $value;
        }
        if (is_array($value)) {
            return $max >= count($value);
        }
        return false;
    }
    /**
     * Validate record is unique in custom database table.
     *
     * @since 1.0.0
     *
     * @param mixed $value Value to check.
     * @param array $parameters Parameters [table, column, ignore_id].
     *
     * @return bool True if unique.
     */
    protected function validate_unique($value, $parameters): bool
    {
        global $wpdb;
        if (count($parameters) < 2) {
            return false;
        }
        $table = $parameters[0];
        $column = $parameters[1];
        // Sanity check.
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $table) || !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $column)) {
            return false;
        }
        $ignore = isset($parameters[2]) ? absint($parameters[2]) : null;
        $value = sanitize_text_field($value);
        $table_name = $wpdb->prefix . $table;
        if ($ignore) {
            $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table_name} WHERE {$column} = %s AND id != %s", $value, $ignore));
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Already sanitized.
        } else {
            $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table_name} WHERE {$column} = %s", $value));
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Already sanitized.
        }
        return 0 === (int) $count;
    }
    /**
     * Validate record exists in custom database table.
     *
     * @since 1.0.0
     *
     * @param mixed $value Value to check.
     * @param array $parameters Parameters [table, column].
     *
     * @return bool True if exists.
     */
    protected function validate_exists($value, $parameters): bool
    {
        global $wpdb;
        if (count($parameters) < 2) {
            return false;
        }
        $table = $parameters[0];
        $column = $parameters[1];
        // Sanity check.
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $table) || !preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $column)) {
            return false;
        }
        $value = sanitize_text_field($value);
        $table_name = $wpdb->prefix . $table;
        $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table_name} WHERE {$column} = %s", $value));
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Identifier validated above.
        return 0 < $count;
    }
    /**
     * Validate date format.
     *
     * @since 1.0.0
     *
     * @param mixed $value Value to validate.
     * @param array $parameters Parameters [format].
     *
     * @return bool True if valid.
     */
    protected function validate_date_format($value, $parameters): bool
    {
        if (!isset($parameters[0]) || !is_string($value)) {
            return false;
        }
        $format = $parameters[0];
        return (new Utils())->date_validate($value, $format);
    }
    /**
     * Add validation error.
     *
     * @since 1.0.0
     *
     * @param string $field Field name.
     * @param string $rule Rule name that failed.
     * @param array  $parameters Rule parameters.
     * @param array  $messages Custom error messages.
     * @param array  $labels Custom field labels.
     *
     * @return void
     */
    protected function add_error($field, $rule, $parameters, $messages, $labels): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = array();
        }
        $field_rule_key = $field . '.' . $rule;
        if (isset($messages[$field_rule_key])) {
            $message = $messages[$field_rule_key];
        } elseif (isset($messages[$rule])) {
            $message = $messages[$rule];
        } else {
            $message = $this->messages[$rule] ?? 'The %s is invalid.';
        }
        $attribute = array_key_exists($field, $labels) ? $labels[$field] : preg_replace('/(?:[^a-zA-Z0-9 ]+|\s+)/', ' ', trim($field));
        $replacements = array_merge(array($attribute), $parameters);
        $replacements = array_filter($replacements, 'is_scalar');
        $this->errors[$field][] = vsprintf($message, $replacements);
    }
}