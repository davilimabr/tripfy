<?php
namespace Classes;

class BingRouteApi
{
    const URL = "http://dev.virtualearth.net/REST/v1/Routes";
    public $WayPoint1 = "";
    public $WayPoint2 = "";
    public $TravelDuration = 0;

    public function __construct($wp1, $wp2, $travelMode)
    {
        $this->WayPoint1 = $wp1;
        $this->WayPoint2 = $wp2;

        $this->CalculateRoute($wp1, $wp2, $travelMode);
    }

    public function CalculateRoute($wp1, $wp2, $travelMode)
    {
        $params = [
            'wp.1'  => $wp1,
            'wp.2'  => $wp2,
            'travelmode' => $travelMode,
            'key'        => getenv('KEY')//get keyApi from .env
        ];

        if(strtolower($travelMode) == 'transit')
            $params['datetime'] = date('h:i:s', time());
       
        $result = $this->SendRequest($params);
        $travel_duration = $result['resources'][0]['travelDuration'];
        
        $this->TravelDuration = $travel_duration;
    }

    private function SendRequest($params = [])
    {
        $url = BingRouteApi::URL . '/' . ucfirst($params['travelmode']);
        unset($params['travelmode']);
        $url .= '?'. http_build_query($params); 

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        
        $result = json_decode($result, true);

        if(isset($result['errorDetails']))
            throw new \Exception(implode($result['errorDetails']));
        else
            return $result['resourceSets'][0];
    }

}

?>