<?php
/**
 * Plugin Name: WP SMTP by AzzLabs
 * Description: Abilitare e configurare SMTP su WordPress
 * Version: 1.0
 * Author: azzari.dev
 * Author URI: https://azzari.dev
 */
defined( 'ABSPATH' ) or die( 'Plugin file cannot be accessed directly.' );

function alwp_smtp_add_menu_entry() { 
    // Registra la voce menu
    add_submenu_page('options-general.php', __('Impostazioni server SMTP', 'alwp'), __('Server SMTP', 'alwp'), 'administrator', 'alwp-smtp-server', 'alwp_smtp_settings_page');
    // Registra i campi delle impostazioni di WP
    add_action('admin_init', 'alwp_smtp_register_settings');
}

function alwp_smtp_register_settings() {
	register_setting('alwp_settings_group', 'alwp_smtp_enabled', ['type' => 'boolean', 'default' => false, 'sanitize_callback' => 'alwp_smtp_sanitize_cb']);
	register_setting('alwp_settings_group', 'alwp_smtp_host', ['type' => 'string', 'default' => 'mail.example.com']);
	register_setting('alwp_settings_group', 'alwp_smtp_port', ['type' => 'string', 'default' => '25']);
    register_setting('alwp_settings_group', 'alwp_smtp_smtpauth', ['type' => 'boolean', 'default' => true, 'sanitize_callback' => 'alwp_smtp_sanitize_cb']);
	register_setting('alwp_settings_group', 'alwp_smtp_username', ['type' => 'string', 'default' => 'username@example.com']);
	register_setting('alwp_settings_group', 'alwp_smtp_password', ['type' => 'string', 'default' => 'yourpassword']);
	register_setting('alwp_settings_group', 'alwp_smtp_smtpsecure', ['type' => 'string', 'default' => 'ssl']);
	register_setting('alwp_settings_group', 'alwp_smtp_smtpautotls', ['type' => 'string', 'default' => false, 'sanitize_callback' => 'alwp_smtp_sanitize_cb']);
}
function alwp_smtp_sanitize_cb($string) {
    return $string == 'true';
}

add_action('admin_menu', 'alwp_smtp_add_menu_entry');

function alwp_smtp_settings_page() { ?>
    <div class="wrap">
        <h1><?php _e('Impostazioni server SMTP', 'alwp') ?></h1>
    
        <form method="post" action="options.php">
            <?php settings_fields('alwp_settings_group'); ?>
            <?php do_settings_sections('alwp_settings_group'); ?>

            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e('Stato server SMTP', 'alwp') ?></th>
                    <td>
                        <input type="hidden" name="alwp_smtp_enabled" value="false">
                        <input type="checkbox" name="alwp_smtp_enabled" id="useSMTP" value="true" <?= get_option('alwp_smtp_enabled') ? 'checked' : ''; ?> />
                        <label for="useSMTP">
                            <?php _e('Usa il server SMTP', 'alwp') ?>
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Indirizzo host SMTP', 'alwp') ?></th>
                    <td><input type="text" name="alwp_smtp_host" value="<?= get_option('alwp_smtp_host') ?>" class="regular-text code" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Porta host SMTP', 'alwp') ?></th>
                    <td><input type="text" name="alwp_smtp_port" value="<?= get_option('alwp_smtp_port') ?>" class="regular-text code" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Autenticazione SMTP', 'alwp') ?></th>
                    <td>
                        <input type="hidden" name="alwp_smtp_smtpauth" value="false">
                        <input type="checkbox" name="alwp_smtp_smtpauth" id="smtpautotls" value="true" <?= get_option('alwp_smtp_smtpauth') ? 'checked' : ''; ?> />
                        <label for="smtpautotls">
                            <?php _e('Usa autenticazione per il server SMTP', 'alwp') ?>
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Username SMTP', 'alwp') ?></th>
                    <td><input type="text" name="alwp_smtp_username" value="<?= get_option('alwp_smtp_username') ?>" class="regular-text" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Password SMTP', 'alwp') ?></th>
                    <td><input type="password" name="alwp_smtp_password" value="<?= get_option('alwp_smtp_password') ?>" class="regular-text" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('SMTP auto TLS', 'alwp') ?></th>
                    <td>
                        <input type="hidden" name="alwp_smtp_smtpautotls" value="false">
                        <input type="checkbox" name="alwp_smtp_smtpautotls" id="smtpauth" value="true" <?= get_option('alwp_smtp_smtpautotls') ? 'checked' : ''; ?> />
                        <label for="smtpauth">
                            <?php _e('Attiva TLS automatico su SMTP', 'alwp') ?>
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><?php _e('Protocollo di cifratura SMTP', 'alwp') ?></th>
                    <?php $secure = get_option('alwp_smtp_smtpsecure') ?>
                    <td><select name="alwp_smtp_smtpsecure" class="regular-text"> 
                        <option value=""></option> 
                        <option value="ssl" <?php if ($secure == 'ssl') echo 'selected' ?>>SSL</option> 
                        <option value="tls" <?php if ($secure == 'tls') echo 'selected' ?>>TLS</option> 
                    </select></td>
                </tr>
            </table>

            <?php submit_button(__('Salva la configurazione', 'alwp')); ?>
        </form>
    </div>

<?php } 

add_action('phpmailer_init', 'alwp_smtp_phpmailer_init');
function alwp_smtp_phpmailer_init( PHPMailer\PHPMailer\PHPMailer $phpmailer ) {
    if (get_option('alwp_smtp_enabled')) {
        $phpmailer->Host = get_option('alwp_smtp_host');
        $phpmailer->Port = get_option('alwp_smtp_port'); // could be different
        $phpmailer->Username = get_option('alwp_smtp_username'); // if required
        $phpmailer->Password = get_option('alwp_smtp_password'); // if required
        $phpmailer->SMTPAuth = get_option('alwp_smtp_smtpauth') == 1; // if required
        $phpmailer->SMTPSecure = get_option('alwp_smtp_smtpsecure'); // enable if required, 'tls' is another possible value
        $phpmailer->SMTPAutoTLS = get_option('alwp_smtp_smtpautotls');
        
        $phpmailer->IsSMTP();
    }
}