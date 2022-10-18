<?php
require_once(INCLUDE_DIR . 'class.plugin.php');
require_once(INCLUDE_DIR . 'class.forms.php');
require_once('config.php');

class reCaptchaField extends FormField
{
    static $g_site_key; 
    static $g_secret_key;

    static $widget = 'reCaptchaWidget';

    function validateEntry($value)
    {
        parent::validateEntry($value);

        if (count(parent::errors()) === 0) {
            $response = json_decode(file_get_contents('https://www.recaptcha.net/recaptcha/api/siteverify?secret=' . self::$g_secret_key . '&response=' . $value));

            if ($response == FALSE) {
                $this->addError('Unable to communicate with the reCaptcha server');
            } elseif (!$response->success) {
                foreach (get_object_vars($response)['error-codes'] as $code) {
                    switch ($code) {
                        case 'missing-input-response':
                            $this->addError($this->getLabel()
                                ? sprintf(__('%s is a required field'), $this->getLabel())
                                : __('This is a required field'));
                            break;
                        case 'invalid-input-response':
                            $this->addError('Your response doesn\'t look right. Please try again');
                            break;
                        case 'no-response':
                            $this->addError('Unable to communicate with the reCaptcha server');
                    }
                }
            }
        }
    }

    function getConfigurationOptions()
    {
        return array(
            'theme' => new ChoiceField(array(
                'label' => 'reCaptcha Theme',
                'choices' => array('dark' => 'Dark', 'light' => 'Light'),
                'default' => 'light',
            )),
            'type' => new ChoiceField(array(
                'label' => 'reCaptcha Type',
                'choices' => array('image' => 'Image', 'audio' => 'Audio'),
                'default' => 'image',
            )),
            'size' => new ChoiceField(array(
                'label' => 'reCaptcha Type',
                'choices' => array('compact' => 'Compact', 'normal' => 'Normal'),
                'default' => 'normal',
            )),
        );
    }
}
class reCaptchaWidget extends Widget
{
    function render()
    {
        # Form field config
        $fconfig = $this->field->getConfiguration();

        ?>
            <div id="<?php echo $this->id; ?>" style="display:flex;justify-content:center;" class="g-recaptcha" data-sitekey="<?php echo reCaptchaField::$g_site_key; ?>" data-theme="<?php echo $fconfig['theme'] ?: 'light'; ?>" data-type="<?php echo $fconfig['type'] ?: 'image'; ?>" data-size="<?php echo $fconfig['size'] ?: 'normal'; ?>"></div>
            <script src="https://www.recaptcha.net/recaptcha/api.js" type="application/javascript" async defer></script>
    <?php
        }
        function getValue()
        {
            if (!($data = $this->field->getSource()))
                return null;
            if (!isset($data['g-recaptcha-response']))
                return null;
            return $data['g-recaptcha-response'];
        }
    }

    class GoogleRecaptchaV2 extends Plugin
    {
        var $config_class = "GoogleRecaptchaV2Config";
        function bootstrap()
        {
            $config = $this->getConfig();
            reCaptchaField::$g_site_key = $config->get('g-site-key'); 
            FormField::addFieldTypes(__('Verification'), function () {
                return array(
                    'recaptcha' => array('Google reCAPTCHA', 'reCaptchaField')
                );
            });
        }
    }
