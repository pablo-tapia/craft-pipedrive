<?php

namespace Craft;

/**
 * Defines actions which can be posted to by forms in our templates
 * @package PipedriveLead
 * @version 1.2.0
 */
class PipedriveLead_ServiceController extends BaseController
{
    /**
     * Allows anonymous access to save lead controller action
     * @var bool
     */
    protected $allowAnonymous = true;

    /**
     * Creates or updates an existing lead based on POST data
     */
    public function actionSaveLead()
    {
        $this->requirePostRequest();

        $attributes = [
            'name' => craft()->request->getPost('name', 'No name provided'),
            'email' => craft()->request->getPost('email'),
            'company' => craft()->request->getPost('company', ''),
            'telephone' => craft()->request->getPost('phone_number', ''),
            'description' => craft()->request->getValidatedPost('body')
        ];
        try {
            $model = new PipedriveLeadModel();
            $model->setAttributes($attributes);
            craft()->pipedriveLead->saveLeadOnPipedrive($model);
            $this->returnJson(['action' => 'Save', 'success' => true]);
        } catch(\CException $e) {
            PipedriveLeadPlugin::log($e->getMessage(), LogLevel::Error, true);
            $this->returnErrorJson($e->getMessage());
        } catch (Exception $e) {
            PipedriveLeadPlugin::log($e->getMessage(), LogLevel::Error, true);
            $this->returnErrorJson($e->getMessage());
        } // end try - catch
    }

    /**
     * Deletes a lead
     * @return string
     */
    public function actionDeleteLead()
    {
        $this->requirePostRequest();
        $this->requireAjaxRequest();
        try {
            $id = craft()->request->getRequiredPost('id');
            $deleteFromPipedrive = craft()->pipedriveLead->deleteLeadFromPipedrive($id);
            if ((bool) $deleteFromPipedrive === true) {
                $this->returnJson(['action' => 'Delete', 'success' => true]);
            } else {
                throw new Exception('Failed to delete Lead on Pipedrive, due to insufficient access levels or not ID was provided.');
            } // end if - else
        } catch (\CException $e) {
            PipedriveLeadPlugin::log($e->getMessage(), LogLevel::Error, false);
            $this->returnErrorJson($e->getMessage());
        } catch (Exception $e) {
            PipedriveLeadPlugin::log($e->getMessage(), LogLevel::Error, false);
            $this->returnErrorJson($e->getMessage());
        } // end try - catch
    }
}