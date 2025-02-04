<?php
namespace afbp;
enum EventAttendanceMode{
    case IN_PERSON = "https://schema.org/OfflineEventAttendanceMode";
    case ONLINE = "https://schema.org/OnlineEventAttendanceMode";
    case IN_PERSON_AND_ONLINE = "https://schema.org/MixedEventAttendanceMode";
}

enum EventStatus{
    case SCHEDULED="https://schema.org/EventScheduled";
    case CANCELED = "https://schema.org/EventCancelled";
    case RESCHEDULED="https://schema.org/EventRescheduled";
    case POSTPONED = "https://schema.org/EventPostponed";
}


class afbp_event_schema{
    
}

class afbp_event_schema_builder{
    public $name;
    public $output = array(
        "@context" => "https://schema.org",
        "@type" => "Event",
        "organizer" => array()
    );

    function __construct($name) {
        $this->name = $name;
    }

    function setDescription($description){
        if(isset($description)){
            $this->output["description"] = $description;
        }
        return $this;
    }

    function setDate($startDateTime, $endDateTime=NULL){
        if(!isset($startDateTime)){
            return $this;
        }
        $start = date_create($startDateTime)->format("c");
        $this->output["startDate"] = $start;


        if(isset($endDateTime)){
            $end = date_create($endDateTime)->format("c");
            $this->output["endDate"] = $end;
        }
        return $this;
    }

    function setStatus($status=EventStatus::SCHEDULED){
        $this->output["status"] = $status;
        return $this;
    }

    function setLocation($name, $streetAddress=NULL, $city=NULL, $state=NULL, $country=NULL, $zip=NULL){
        if(!isset($name)){
            return $this;
        }
        $location = array(
            "@type" => "Place",
            "name" => $name
        );
        if(isset($streetAddress)){
            $address = array(
                "streetAddress" => $streetAddress,
                "addressLocality" => isset($city) ? $city:"",
                "postalCode" => isset($zip) ? $zip : "",
                "addressRegion" => isset($state) ? $state:"",
                "addressCountry" => isset($country) ? $country:""
            );
            $location["address"] = $address;
        }
        $this->output["location"] = $location;
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

    
    function setPerformer($name, $type="Performing Group"){
        if(!isset($name)){
            return $this;
        }
        $performer = array(
            "@type" => $type,
            "name" => $name
        );
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
        $json = json_encode($this->output, JSON_PRETTY_PRINT);
        print($json);
        return $json;
    }

}

$tester = new afbp_event_schema_builder("Event for testing")->setDate("1-1-2025 12:00pm")
->setLocation("Atlanta Theater", "4511 whatever st")
->setOrganizer("Atlanta Freedom Bands", "https://atlantafreedombands.com")
->build();


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