<?php
namespace afbp;
use DateTime;

enum EventType{
    case VIRTUAL;
    case IN_PERSON;
    case IN_PERSON_AND_ONLINE;
}

interface EventAttendanceMode{
    public const IN_PERSON = "https://schema.org/OfflineEventAttendanceMode";
    public const ONLINE = "https://schema.org/OnlineEventAttendanceMode";
    public const IN_PERSON_AND_ONLINE = "https://schema.org/MixedEventAttendanceMode";
}

enum EventStatus{
    public const SCHEDULED="https://schema.org/EventScheduled";
    public const CANCELED = "https://schema.org/EventCancelled";
    public const RESCHEDULED="https://schema.org/EventRescheduled";
    public const POSTPONED = "https://schema.org/EventPostponed";
}



interface EventManager{
    public const EVENT_POST = "EM_POST_TYPE_EVENT";
    public const META_EVENT_START = "_event_start_local";
    public const META_EVENT_END = "_event_end_local";

    public const META_LOCATION_ID = "_location_id";
}


class afbp_event_schema{
    private $postId;
    private $name;
    private $description;
    private $featuredImage;
    private $location_id;
    private $category_list;
    private $schema;
    public function __construct($postId) {
        $this->postId = $postId;
        $this->name = get_the_title($postId);
        $this->description = wp_strip_all_tags(get_the_excerpt($postId));
        $this->featuredImage = get_the_post_thumbnail_url($postId);
        $this->location_id = $this->get_meta("_location_id");

        $categories = get_categories(array("taxonomy" => 'event-categories',"hide-empty" => true));
        $this->category_list = array_map(fn($cat):string => $cat->name, $categories);
    }

    private function get_meta($key, $postId=NULL){
        if(!isset($postId)){
            $postId = $this->postId;
        }
        if(!isset($postId)){ return; }

        return get_post_meta( $postId, $key, true );
    }


    public function generateSchema(){
        $options = get_option( 'afbp_event_schema_option_name' );
        $builder = new afbp_event_schema_builder($this->name);
        $builder->setDate($this->get_meta(EventManager::META_EVENT_START), $this->get_meta(EventManager::META_EVENT_END)) //TODO: adjust for timezone
        ->setImages($this->featuredImage)
        ->setLocation($this->location_id)
        ->setDescription($this->description)
        ->setOrganizer($options["organizer_name"], $options["organizer_url"])
        ->setStatus();

        foreach($this->category_list as $category){
            $builder->addPerformer($category);
        }

        $newSchema = $builder->build();
        $this->schema = $newSchema;
        return $this;
    }

    function addToPage(){
        echo '<script type="application/ld+json">'.$this->schema."</script>";
    }

    public function debug($string=""){
        $scriptName = "event-schema-debug";
        global $post;
        wp_register_script($scriptName, plugin_dir_url( "./afb-parade/build/js" )."js/event-schema-debugger.js");
        wp_localize_script($scriptName, 'debug_payload', Array(
            'schema' => utf8_uri_encode($this->schema),
            'payload' => utf8_uri_encode($string),
            'post' => $post->ID
            )
        );
        wp_enqueue_script($scriptName);
    }
}

class afbp_event_schema_builder{
    private $output = array(
        "@context" => "https://schema.org",
        "@type" => "Event",
        "organizer" => array()
    );

    function __construct($name) {
        $this->output["name"] = $name;
    }

    function setDescription($description){
        if(isset($description)){
            $this->output["description"] = $description;
        }
        return $this;
    }

    function setDate($startDateTime, $endDateTime=NULL, $timezoneOffset=NULL){
        //NOTE: Google schema will set the time zone based on the location of the event
        //We could define it explicitly, but EventManager has a bajillion time zones that
        //are not all recognized by the schema.
        $formatString = "Y-m-d\TH:i:s";

        if(!isset($startDateTime)){
            return $this;
        }
        $start = new DateTime($startDateTime);
        $start = $start->format($formatString);
        $this->output["startDate"] = $start;


        if(isset($endDateTime)){
            $end = new DateTime($endDateTime);
            $end = $end->format($formatString);
            $this->output["endDate"] = $end;
        }
        return $this;
    }

    function setStatus($status=EventStatus::SCHEDULED){
        $this->output["status"] = $status;
        return $this;
    }

    function setLocation(string $location_id){
        $location = new EventLocation($location_id);
        $this->output["location"] = $location->asArray();
        return $this;
    }

    function setImages($imageUrls){
        if(!isset($imageUrls)){
            return $this;
        }
        if(is_array($imageUrls)){
            $output["image"] = $imageUrls;
        }else{
            $output["image"] = array($imageUrls);
        }
        return $this;
    }

    
    function addPerformer($name, $type="Performing Group"){
        if(!isset($name)){
            return $this;
        }
        if(isset($this->output["performer"])){
            $performer = $this->output["performer"];
        }else{
            $performer = array();
        }
        $performerToAdd = array(
                "@type" => $type,
                "name" => $name
            );
        array_push($performer, $performerToAdd);
        $this->output["performer"] = $performer;
        return $this;
    }

    function setOrganizer($name, $url=NULL, $type="Organization"){
        if(!isset($name)){
            return $this;
        }
        $organizer = array(
            "@type" => $type,
            "name" => $name,
            "url" => isset($url) ? $url:""
        );
        $this->output["organizer"] = $organizer;
        return $this;
    }

    function build(){
        $json = json_encode($this->output); // JSON_PRETTY_PRINT); //can prettyprint for debugging
        return $json;
    }

}

class EventLocation{
    private $location_id;
    private $event_type = EventType::IN_PERSON;
    private array $location;
    public function __construct(String $location_id) {
        if(!isset($location_id)){
            $this->event_type = EventType::VIRTUAL;
        }
        $this->location_id = $location_id;
    }

    public function asArray(){
        global $wpdb;
        $location_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix. "em_locations WHERE location_id = %d", $this->location_id ) );
        return Array(
            "@type" => "Place",
            "name" => $location_data->location_name,
            "address" => Array(
                "streetAddress" => $location_data->location_address,
                "addressLocality" => $location_data->location_town,
                "postalCode"=> $location_data->location_postcode,
                "addressRegion" => $location_data->location_state,
                "addressCountry" => $location_data->location_country,
            )
        );
    }
}

function createSchema(){
    $post_id = get_the_ID();

    //Exit the function if we're either not on an Event Manager event page
    //or if the em_is_event global function set by Event Manager isn't defined.
    if(!function_exists('em_is_event') || !em_is_event($post_id)){
        return; 
    }
    
    $schema = new afbp_event_schema($post_id);
    echo $schema -> generateSchema() -> addToPage();
}

// {
//     "@context": "https://schema.org",
//     "@type": "Event",
//     "name": "The Adventures of Kira and Morrison",
//     "startDate": "2025-07-21T19:00-05:00",
//     "endDate": "2025-07-21T23:00-05:00",
//     "eventAttendanceMode": "https://schema.org/OfflineEventAttendanceMode",
//     "eventStatus": "https://schema.org/EventScheduled",
//     "location": {
//       "@type": "Place",
//       "name": "Snickerpark Stadium",
//       "address": {
//         "@type": "PostalAddress",
//         "streetAddress": "100 West Snickerpark Dr",
//         "addressLocality": "Snickertown",
//         "postalCode": "19019",
//         "addressRegion": "PA",
//         "addressCountry": "US"
//       }
//     },
//     "image": [
//       "https://example.com/photos/1x1/photo.jpg",
//       "https://example.com/photos/4x3/photo.jpg",
//       "https://example.com/photos/16x9/photo.jpg"
//      ],
//     "description": "The Adventures of Kira and Morrison is coming to Snickertown in a can't miss performance.",
//     "offers": {
//       "@type": "Offer",
//       "url": "https://www.example.com/event_offer/12345_202403180430",
//       "price": 30,
//       "priceCurrency": "USD",
//       "availability": "https://schema.org/InStock",
//       "validFrom": "2024-05-21T12:00"
//     },
//     "performer": {
//       "@type": "PerformingGroup",
//       "name": "Kira and Morrison"
//     },
//     "organizer": {
//       "@type": "Organization",
//       "name": "Kira and Morrison Music",
//       "url": "https://kiraandmorrisonmusic.com"
//     }
//   }

?>