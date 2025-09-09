<?php
/**
 * Plugin Name: Contact/Reborn Email Validator
 * Description: Validates email addresses using Contact/Reborn API
 * Version: 1.0.0
 * Author: Your Name
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Load Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

use ContactReborn\ContactRebornClient;
use ContactReborn\Exceptions\ApiException;

class ContactRebornWordPress
{
    private $client;
    private $optionName = 'contactreborn_settings';
    
    public function __construct()
    {
        // Initialize hooks
        add_action('admin_menu', [$this, 'addAdminMenu']);
        add_action('admin_init', [$this, 'registerSettings']);
        
        // Initialize API client if token is set
        $settings = get_option($this->optionName);
        if (!empty($settings['api_token'])) {
            $this->client = new ContactRebornClient($settings['api_token']);
            
            // Hook into Contact Form 7
            add_filter('wpcf7_validate_email', [$this, 'validateEmailCF7'], 10, 2);
            add_filter('wpcf7_validate_email*', [$this, 'validateEmailCF7'], 10, 2);
            
            // Hook into Gravity Forms
            add_filter('gform_field_validation', [$this, 'validateEmailGravity'], 10, 4);
            
            // Hook into WPForms
            add_action('wpforms_process_validate_email', [$this, 'validateEmailWPForms'], 10, 3);
        }
    }
    
    /**
     * Add admin menu
     */
    public function addAdminMenu()
    {
        add_options_page(
            'Contact/Reborn Settings',
            'Contact/Reborn',
            'manage_options',
            'contactreborn',
            [$this, 'settingsPage']
        );
    }
    
    /**
     * Register settings
     */
    public function registerSettings()
    {
        register_setting($this->optionName, $this->optionName);
        
        add_settings_section(
            'contactreborn_main',
            'API Settings',
            null,
            'contactreborn'
        );
        
        add_settings_field(
            'api_token',
            'API Token',
            [$this, 'apiTokenField'],
            'contactreborn',
            'contactreborn_main'
        );
        
        add_settings_field(
            'block_on_error',
            'Block on API Error',
            [$this, 'blockOnErrorField'],
            'contactreborn',
            'contactreborn_main'
        );
    }
    
    /**
     * Settings page HTML
     */
    public function settingsPage()
    {
        ?>
        <div class="wrap">
            <h1>Contact/Reborn Email Validator</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields($this->optionName);
                do_settings_sections('contactreborn');
                submit_button();
                ?>
            </form>
            
            <?php if ($this->client): ?>
                <h2>Test Email Check</h2>
                <form method="post">
                    <input type="email" name="test_email" placeholder="test@example.com" />
                    <input type="submit" name="test_submit" value="Test" class="button" />
                </form>
                
                <?php
                if (isset($_POST['test_submit']) && !empty($_POST['test_email'])) {
                    $this->testEmail($_POST['test_email']);
                }
                ?>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * API Token field
     */
    public function apiTokenField()
    {
        $settings = get_option($this->optionName);
        $value = $settings['api_token'] ?? '';
        echo '<input type="text" name="' . $this->optionName . '[api_token]" value="' . 
             esc_attr($value) . '" class="regular-text" />';
        echo '<p class="description">Get your API token from Contact/Reborn dashboard</p>';
    }
    
    /**
     * Block on error field
     */
    public function blockOnErrorField()
    {
        $settings = get_option($this->optionName);
        $checked = !empty($settings['block_on_error']) ? 'checked' : '';
        echo '<input type="checkbox" name="' . $this->optionName . '[block_on_error]" ' . 
             $checked . ' value="1" />';
        echo '<p class="description">Block email submission if API is unavailable</p>';
    }
    
    /**
     * Test email functionality
     */
    private function testEmail($email)
    {
        try {
            $result = $this->client->checkEmail($email);
            
            echo '<div class="notice notice-info"><p>';
            echo 'Email: ' . esc_html($email) . '<br>';
            echo 'Is Spam: ' . ($result['is_spam'] ? 'Yes' : 'No') . '<br>';
            if ($result['reason']) {
                echo 'Reason: ' . esc_html($result['reason']) . '<br>';
            }
            echo 'Confidence: ' . esc_html($result['confidence']) . '<br>';
            echo '</p></div>';
            
        } catch (ApiException $e) {
            echo '<div class="notice notice-error"><p>';
            echo 'API Error: ' . esc_html($e->getMessage());
            echo '</p></div>';
        }
    }
    
    /**
     * Contact Form 7 validation
     */
    public function validateEmailCF7($result, $tag)
    {
        $email = isset($_POST[$tag->name]) ? sanitize_email($_POST[$tag->name]) : '';
        
        if (!empty($email)) {
            try {
                $check = $this->client->checkEmail($email);
                if ($check['is_spam']) {
                    $message = $check['reason'] ?? 'This email address is not allowed.';
                    $result->invalidate($tag, $message);
                }
            } catch (ApiException $e) {
                $this->handleApiError($result, $tag, $e);
            }
        }
        
        return $result;
    }
    
    /**
     * Gravity Forms validation
     */
    public function validateEmailGravity($result, $value, $form, $field)
    {
        if ($field->type != 'email' || $result['is_valid'] === false) {
            return $result;
        }
        
        try {
            $check = $this->client->checkEmail($value);
            if ($check['is_spam']) {
                $result['is_valid'] = false;
                $result['message'] = $check['reason'] ?? 'This email address is not allowed.';
            }
        } catch (ApiException $e) {
            $settings = get_option($this->optionName);
            if (!empty($settings['block_on_error'])) {
                $result['is_valid'] = false;
                $result['message'] = 'Email validation temporarily unavailable.';
            }
            error_log('ContactReborn API Error: ' . $e->getMessage());
        }
        
        return $result;
    }
    
    /**
     * WPForms validation
     */
    public function validateEmailWPForms($field_id, $field_submit, $form_data)
    {
        $email = sanitize_email($field_submit);
        
        if (!empty($email)) {
            try {
                $check = $this->client->checkEmail($email);
                if ($check['is_spam']) {
                    $message = $check['reason'] ?? 'This email address is not allowed.';
                    wpforms()->process->errors[$form_data['id']][$field_id] = $message;
                }
            } catch (ApiException $e) {
                $settings = get_option($this->optionName);
                if (!empty($settings['block_on_error'])) {
                    wpforms()->process->errors[$form_data['id']][$field_id] = 
                        'Email validation temporarily unavailable.';
                }
                error_log('ContactReborn API Error: ' . $e->getMessage());
            }
        }
    }
    
    /**
     * Handle API errors
     */
    private function handleApiError($result, $tag, $exception)
    {
        $settings = get_option($this->optionName);
        if (!empty($settings['block_on_error'])) {
            $result->invalidate($tag, 'Email validation temporarily unavailable.');
        }
        error_log('ContactReborn API Error: ' . $exception->getMessage());
    }
}

// Initialize plugin
new ContactRebornWordPress();