<?php 
require_once INCLUDE_DIR . 'class.plugin.php';

class GoogleRecaptchaV2Config extends PluginConfig{
    function getOptions() {       
        return array(
            'google' => new SectionBreakField(array(
                'label' => 'Google Recaptcha Verification',
            )),
            'g-site-key' => new TextboxField(array(
                'label' => 'Site Key',
                'required'=> true,
                'configuration' => array('size'=>60, 'length'=>100, 'autocomplete'=>'off'),
            )),
            'g-secret-key' => new TextboxField(array(
                'widget'=>'PasswordWidget',
                'required'=>true,
                'label' => 'Secret Key',
                'configuration' => array('size'=>60, 'length'=>100),
            ))
        );
    }
    function pre_save($config, &$errors) {
        // Todo: verify key
        if (!function_exists('curl_init')) {
            Messages::error('CURL extension is required');
            return false;
        }
        global $msg;
        if (!$errors)
            $msg = 'Successfully updated reCAPTCHA settings';
        return true;
    }
}