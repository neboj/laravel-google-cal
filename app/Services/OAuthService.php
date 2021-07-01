<?php

namespace App\Services;

use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google_Client;
use Google_Service_Calendar;

class OAuthService
{
    /**
     * @var Google_Client
     */
    private $client;
    /**
     * @var Google_Service_Calendar
     */
    private $service;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->client = $this->getClient();
        $this->service = new Google_Service_Calendar($this->client);
    }

    private function getClient(){
        // Get the API client and construct the service object.
        $client = new Google_Client();
        $client->setApplicationName('Google Calendar API PHP Quickstart');
        $client->setScopes(Google_Service_Calendar::CALENDAR_READONLY);
        $client->setAuthConfig(storage_path('json/credentials.json'));
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.
        $tokenPath = storage_path('json/token.json');
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));

                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new \Exception(join(', ', $accessToken));
                }
            }
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }
        return $client;
    }

    public function listEvents()
    {
        // Print the next 10 events on the user's calendar.
        $calendarId = 'primary';
        $optParams = array(
            'maxResults' => 10,
            'orderBy' => 'startTime',
            'singleEvents' => true,
            'timeMin' => date('c'),
        );
        $results = $this->service->events->listEvents($calendarId, $optParams);
        $events = $results->getItems();

        if (empty($events)) {
            print "No upcoming events found.\n";
        } else {
            print "Upcoming events:\n";
            foreach ($events as $event) {
                $start = $event->start->dateTime;
                if (empty($start)) {
                    $start = $event->start->date;
                }
                printf("%s (%s)\n", $event->getSummary(), $start);
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function createEvent($data){
        $calendarId = 'primary';

        $optParams = [
            'sendNotifications' => true,
            'sendUpdates' => 'all'
        ];
        $attendee = new Calendar\EventAttendee();
        $attendee->setEmail($data->attendees[0]->email);

        $event = new Event();
        $event->attendees = [$attendee];
        $event->setSummary($data->summary);
        $event->setDescription($data->description);

        $startMeetingDate = new Calendar\EventDateTime();
        $startMeetingDate->setTimeZone($data->start->timeZone);
        $startMeetingDate->setDateTime((new \DateTime($data->start->dateTime,new \DateTimeZone($data->start->timeZone)))->format(\DateTime::RFC3339));

        $endMeetingDate = new Calendar\EventDateTime();
        $endMeetingDate->setTimeZone($data->end->timeZone);
        $endMeetingDate->setDateTime((new \DateTime($data->end->dateTime,new \DateTimeZone($data->end->timeZone)))->format(\DateTime::RFC3339));

        $event->setStart($startMeetingDate);
        $event->setEnd($endMeetingDate);

        $reminder = new Calendar\EventReminder();
        $reminder->setMethod('email');
        $reminder->setMinutes(30);
        $reminder2 = new Calendar\EventReminder();
        $reminder2->setMethod('email');
        $reminder2->setMinutes(15);

        $reminders = new Calendar\EventReminders();
        $reminders->setUseDefault(false);
        $reminders->setOverrides([$reminder, $reminder2]);


        $event->setReminders($reminders);

        $results = $this->service->events->insert($calendarId, $event , $optParams);

//        print_r($results);
        return $results;
    }

}
