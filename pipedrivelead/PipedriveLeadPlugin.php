<?php

namespace Craft;

/**
 * PipeDrive plugin that allows to register new leads
 * @package		Pipedrive Plugin
 * @version		1.2.0
 */
class PipedriveLeadPlugin extends BasePlugin
{
	/**
	 * Returns the user-facing name
	 * @return string
	 */
	public function getName()
	{
		return Craft::t('Pipedrive');
	}

	/**
	 * Returns the plugin version
	 * @return string
	 */
	public function getVersion()
	{
		return '1.2.0';
	}

	/**
	 * Returns the developer's name
	 * @return string
	 */
	public function getDeveloper()
	{
		return '45RPM LLC';
	}

	/**
	 * Returns the developer's website URL
	 * @return string
	 */
	public function getDeveloperUrl()
	{
		return 'http://www.45rpm.co';
	}

	/**
	 * Returns a description for the plugin
	 * @return string
	 */
	public function getDescription()
	{
		return 'A simple plugin that allows to create a Deal on Pipedrive.';
	}

	/**
	 * Define plugin settings for configuration
	 * @return array
	 */
	protected function defineSettings()
	{
		return [
			'apiUrl' => [AttributeType::Url, 'default' => 'https://api.pipedrive.com/v1', 'required' => true],
			'apiToken' => [AttributeType::String, 'required' => true],
            'ownerId' => [AttributeType::Number, 'required' => true],
		];
	}

    /**
     * Set the HTML for the settings section of the plugin
     * @return array
     */
    public function getSettingsHtml()
    {
        return craft()->templates->render('pipedrivelead/_settings', ['settings' => $this->getSettings()]);
    }

	/**
	 * Allow us to catch the beforeSend event from the Contact form
	 * and send the request to create both the Organization and the Person
	 * in Pipedrive.
	 */
	public function init()
	{
		craft()->on('contactForm.beforeSend', function(ContactFormEvent $event)
		{
            /**
             * In case we have another plugin working with the beforeSend event
             * let's check the fake flag is still false
             */
            if (!$event->fakeIt) {
                try {
                    $attributes = [
                        'name' => craft()->request->getPost('fromName', 'No name provided'),
                        'email' => craft()->request->getPost('fromEmail'),
                        'company' => craft()->request->getPost('message.Company', ''),
                        'telephone' => craft()->request->getPost('message.Telephone', ''),
                        'description' => craft()->request->getValidatedPost('message.body'),
                    ];
                    $model = new PipedriveLeadModel();
                    $model->setAttributes($attributes);
                    craft()->pipedriveLead->saveLeadOnPipedrive($model);
                    return true;
                } catch(\CException $e) {
                    PipedriveLeadPlugin::log('Unable to create Lead object in Pipedrive. Here is the exception: ' . $e->getMessage(), LogLevel::Error, false);
                } catch (Exception $e) {
                    PipedriveLeadPlugin::log('Unable to create Lead object in Pipedrive. Here is the exception: ' . $e->getMessage(), LogLevel::Error, false);
                } // end try - catch
            } // end if
            $event->fakeIt = true;
		});
	}
}