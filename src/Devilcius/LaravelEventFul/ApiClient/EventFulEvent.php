<?php

namespace Devilcius\LaravelEventFul\ApiClient;

/**
 * Description of EventFulEvent
 *
 * @author Marcos Peña
 */
class EventFulEvent extends BaseEventFul
{

    private $call = '/events/search';

    /**
     * Get all events in a specific location by country or city name.
     * @param array $options An array with the following required values: <i>location</i> and optional values: <i>distance</i>, <i>page</i>
     * @return array
     */
    public function getEvents($options)
    {
        // Check for required variables
        if (!empty($options['location'])) {
            $response = $this->call($this->call, $options);
            if (!is_object($response)) {
                
                throw new \RuntimeException("Wrong response");
            }
            if($response->events === null) {
                //No events found for this location;
                return false;
            }
            $events['currentPage'] = (string) $response->page_number;            
            $events['totalPages'] = (string) $response->page_count;
            $events['totalResults'] = (string) $response->total_items;
            $i = 0;
            foreach ($response->events->event as $event) {
                if (!is_object($event)) {
                    continue;
                }
                $events['events'][$i]['id'] = (string) $event->id;
                $events['events'][$i]['title'] = (string) $event->title;
                $ii = 0;
                if (!$event->performers) { //often artist name is included in title, and $event->performers is null
                    $events['events'][$i]['artists'][0] = str_replace(strtolower($options['location']), "", strtolower($event->title));
                } else {
                    foreach ($event->performers as $artist) {
                        if(is_object($artist)) {
                            $events['events'][$i]['artists'][$ii] = (string) $artist->name;
                        } elseif(is_array($artist)) { //same artist, different name synthax
                            $events['events'][$i]['artists'][$ii] = (string) $artist[0]->name;
                        } else {
                            $events['events'][$i]['artists'][$ii] = $artist;
                        }                        
                        $ii++;
                    }
                }
                $events['events'][$i]['headliner'] = $events['events'][$i]['artists'][0];
                $events['events'][$i]['venue']['name'] = (string) $event->venue_name;
                $events['events'][$i]['venue']['location']['city'] = (string) $event->city_name;
                $events['events'][$i]['venue']['location']['country'] = (string) $event->country_name;
                $events['events'][$i]['venue']['location']['street'] = (string) $event->venue_address;
                $events['events'][$i]['venue']['location']['postalcode'] = (string) $event->postal_code;
                $events['events'][$i]['venue']['location']['point']['lat'] = (string) $event->latitude;
                $events['events'][$i]['venue']['location']['point']['long'] = (string) $event->longitude;
                $events['events'][$i]['venue']['url'] = (string) $event->venue_url;
                $events['events'][$i]['startTime'] = strtotime(trim((string) $event->start_time));
                $events['events'][$i]['startDate'] = $events['events'][$i]['startTime'];
                $events['events'][$i]['description'] = (string) $event->description;
                $events['events'][$i]['attendance'] = (string) $event->going_count;
                $events['events'][$i]['url'] = (string) $event->url;
                $i++;
            }

            return $events;
        } else {
            throw new \RuntimeException("Location not provided");
        }
    }

}
