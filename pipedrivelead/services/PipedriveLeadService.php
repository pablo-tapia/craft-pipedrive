<?php

namespace Craft;

/**
 * Pipedrive Leads Service
 * @package	Pipedrive Leads
 * @version	1.2.0
 */
class PipedriveLeadService extends BaseApplicationComponent
{
	/**
	 * The URL of the Pipedrive API
	 * @var string
	 */
	private $_url = '';
	/**
	 * ID of the user who's gonna own the deals
	 */
	private $_ownerID = 0;

	/**
	 * Prepares the URL to start making requests to Pipedrive API
	 * @throws Exception when the API Token is not defined
	 */
	protected function _setUrlForRequests()
	{
		$settings = craft()->plugins->getPlugin('pipedrivelead')->getSettings();
		if (!isset($settings->apiToken)) {
			throw new Exception('Unable to make requests without a valid token, please provide one.');
		} // end if

		$this->_url = $settings->apiUrl .'/%s?api_token=' . $settings->apiToken;
		$this->_ownerID = $settings->ownerId;
	}

	/**
	 * Creates a new Deal using the data from the contact form we also attached the body of the
     * message on a note inside the deal
	 * @param PipedriveLeadModel $model - The data been used on the contact form
     * @param string $source - The platform where the lead is coming from, default to the site
	 * @return bool
     * @throws Exception
	 */
	public function saveLeadOnPipedrive(PipedriveLeadModel &$model, $source = 'Website')
	{
		$name = $model->getAttribute('name');
		$dealData = [
			'title' => $source .' - '. $name,
            'user_id' => $this->_ownerID,
            'status' => "open",
            'visible_to' => 3,
		];

        $this->_setUrlForRequests();

        /**
         * Create deal first
         */
        $singleDeal = curl_init();
        $options = [
            CURLOPT_URL => sprintf($this->_url, 'deals'),
            CURLOPT_POSTFIELDS => json_encode($dealData),
            CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => 1,
        ];

        curl_setopt_array($singleDeal, $options);
        $dealResponse = json_decode(curl_exec($singleDeal));
        curl_close($singleDeal);

        if ($dealResponse->success === false) {
            throw new Exception($dealResponse->error);
        } // end if

        /**
         * Now add a note
         */
        $noteData = [
            'content' => $model->getAttribute('description'),
            'deal_id' => $dealResponse->data->id,
        ];

        $singleNote = curl_init();
        $options = [
            CURLOPT_URL => sprintf($this->_url, 'notes'),
            CURLOPT_POSTFIELDS => json_encode($noteData),
            CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
            CURLOPT_POST => 1,
            CURLOPT_RETURNTRANSFER => 1,
        ];

        curl_setopt_array($singleNote, $options);
        $noteResponse = json_decode(curl_exec($singleNote));
        curl_close($singleNote);

        if ($noteResponse->success === false) {
            throw new Exception($noteResponse->error);
        } // end if
        return true;
	}

    /**
     * Deletes the deal from Pipedrive
     * @param int $id - The identifier of the lead (i.e. deal ID)
     * @return bool
     * @throws Exception
     */
    public function deleteLeadFromPipedrive($id)
    {
        if (!empty($id)) {
            $this->_setUrlForRequests();
            $single = curl_init();
            $options = [
                CURLOPT_URL => sprintf($this->_url, 'deals/' . $id),
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_CUSTOMREQUEST => 'DELETE',
            ];
            curl_setopt_array($single, $options);
            $delete = json_decode(curl_exec($single));
            curl_close($single);
            if ($delete->success === false) {
                throw new Exception($delete->error);
            } // end if
            return true;
        } // end if
        return false;
    }
}