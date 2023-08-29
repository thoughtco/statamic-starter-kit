<?php

namespace App\Listeners;

// use \DrewM\MailChimp\MailChimp;

use Statamic\Events\FormSubmitted;
use Statamic\Facades\Entry;
use Statamic\Facades\GlobalSet;
use Statamic\Support\Str;

/*

    CAMPAIGN MONITOR
        + Install https://packagist.org/packages/bashy/laravel-campaignmonitor
        + ENV Requires:
            -> CAMPAIGNMONITOR_LIST_ID
            -> CAMPAIGNMONITOR_API_KEY
            -> CAMPAIGNMONITOR_CLIENT_ID
        + For custom fields, see add function on https://github.com/campaignmonitor/createsend-php/blob/master/csrest_subscribers.php

    MAILCHIMP
        + Install https://packagist.org/packages/drewm/mailchimp-api
        + ENV Requires:
            -> MAILCHIMP_API
            -> MAILCHIMP_AUDIENCE

*/

class FormListener
{
    /**
     * Handle the form submitted event.
     */
    public function handle(FormSubmitted $event)
    {

        switch ($event->submission->form()->handle())
        {
            case 'newsletter':

                // what provider as we using?
                $provider = GlobalSet::findByHandle('mailing_list')->in('default')->get('provider');

                if ($provider != ''){

                    // pass email address, name, service type
                    // type can be mailchimp or campaign_monitor
                    $this->processNewsletter($event->submission->data()['email_address'], $event->submission->data()['name'], [], $provider);

                }

            break;

        }

    }

    // process newsletter signups
    public function processNewsletter($email, $name, $customFields, $type){

        switch ($type){

            case 'campaign_monitor':

                // Add a subscriber to a list
                $result = false;
                $listId = GlobalSet::findByHandle('settings')->in('default')->get('cm_list_id');
                $apiKey = GlobalSet::findByHandle('settings')->in('default')->get('cm_api_key');

                if ($listId != '' && $apiKey != '') {

                    $result = (new \Bashy\CampaignMonitor\CampaignMonitor(app(), $apiKey))
                        ->subscribers($listId)
                        ->add([
                            'EmailAddress' => $email,
                            'Name' => $name,
                            'CustomFields' => [$customFields],
                            'Resubscribe' => true,
                            'ConsentToTrack' => 'No', // Yes, No, or Unchanged - now required by API v3.2
                    ]);

                }

                return $result;

            break;


        }

    }

}

?>
