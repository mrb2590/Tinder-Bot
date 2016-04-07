<?php
/**
 * class.Tinder.php
 * 
 * This software allows easy interaction with the Tinder API
 * 
 * @author Mike Buonomo <mrb2590@gmail.com>
 */

class Tinder
{
    /**
     * @var int $fbId  Facebook ID of the user
     */
    private $fbId;

    /**
     * @var string $fbToken  Facebook access token of the user
     */
    private $fbToken;

    /**
     * @var string $tinderToken  Tinder token of the user
     */
    private $tinderToken;

    /**
     * @var string $api  The Tinder API URL
     */
    private static $api = 'https://api.gotinder.com';

    /**
     * @var array $curlHeaders  Rrequest headers shared by all requests
     */
    private static $curlHeaders = array(
        'User-Agent: Tinder/4.0.9 (iPhone; iOS 8.0.2; Scale/2.00)'
    );

    /**
     * Instantiates object and authorizes user, providing the Tinder token
     *
     * @var int $fbId  Facebook ID of the user
     * @var string $fbToken  Facebook access token of the user
     */
    function __construct($fbId, $fbToken)
    {
        $this->fbId = (int) $fbId;
        $this->fbToken = $fbToken;
        $this->tinderToken = $this->authenticate();
    }

    /**
     * Sends a somewhat customizable cURL request (GET or POST only)
     *
     * @var string $url  URL to send data
     * @var array $additionalHeaders  Any additional headers to be sent with the request
     * @var array $post  Body of a POST (set to true to send an empty post)
     * @return object  JOSN decoded response of the server
     */
    private function sendRequest($url, $additionalHeaders = array(), $post = array())
    {
        $ch = curl_init();
        $headers = self::$curlHeaders;
        if (!empty($additionalHeaders)) {
            foreach ($additionalHeaders as $header) {
                $headers[] = $header;
            }
        }
        $options = array(
            CURLOPT_URL             => $url,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_SSL_VERIFYHOST  => false,
            CURLOPT_SSL_VERIFYPEER  => false,
            CURLOPT_HTTPHEADER      => $headers
        );
        if (!empty($post)) {
            if ($post === true) {
                $options[CURLOPT_POST] = true;
            } else {
                $options[CURLOPT_POST] = true;
                $options[CURLOPT_POSTFIELDS] = $post;
            }
        }
        curl_setopt_array($ch, $options);
        $response = json_decode(curl_exec($ch));
        curl_close($ch);
        return $response;
    }

    /**
     * Authenticates the Facebook user
     *
     * @return object  JOSN response of the server
     */
    private function authenticate()
    {
        $post = json_encode(array(
            'facebook_token' => $this->fbToken,
            'facebook_id'    => $this->fbId,
        ));
        $url = self::$api.'/auth';
        $additionalHeaders = array('Content-Type: application/json');
        $response = $this->sendRequest($url, $additionalHeaders, $post);
        return $response->token;
    }

    /**
     * Get recommentdations of people to match with
     *
     * @return object  JOSN response of the server
     */
    public function getRecommendations()
    {
        $url = self::$api.'/user/recs';
        $additionalHeaders = array(
            'X-Auth-Token: '.$this->tinderToken,
            'Content-Type: application/json'
        );
        return $this->sendRequest($url, $additionalHeaders);
    }

    /**
     * Update a user's Tinder profile
     *
     * @var int $gender  Gender of the user (0: male, 1: female)
     * @var int $ageMin  Min age of people to recommend
     * @var int $ageMax  Max age of people to recommend
     * @var int $distance  Distance in miles of people to recommend
     * @return object  JOSN response of the server
     */
    public function updateProfile($gender, $ageMin, $ageMax, $distance)
    {
        $post = json_encode(array(
            'gender'          => (int) $gender,
            'age_filter_min'  => (int) $ageMin,
            'age_filter_max'  => (int) $ageMax,
            'distance_filter' => (int) $distance
        ));
        $url = self::$api.'/profile';
        $additionalHeaders = array(
            'X-Auth-Token: '.$this->tinderToken,
            'Content-Type: application/json'
        );
        return $this->sendRequest($url, $additionalHeaders, $post);
    }

    /**
     * Reports a user on Tinder
     *
     * @var string $userId  User ID of who to report
     * @var int $cause  Reason for reporting (1: spam, 2: inappropriate/offensive)
     * @return object  JOSN response of the server
     */
    public function reportUser($userId, $cause)
    {
        $post = json_encode(array(
            'cause' => (int) $cause
        ));
        $url = self::$api.'/report/'.$userId;
        $additionalHeaders = array(
            'X-Auth-Token: '.$this->tinderToken,
            'Content-Type: application/json'
        );
        return $this->sendRequest($url, $additionalHeaders, $post);
    }

    /**
     * Sends a message to a match on Tinder (non match will result in error message)
     *
     * @var string $userId  User ID of who to report
     * @var string $message  Message to send to user
     * @return object  JOSN response of the server
     */
    public function sendMessage($userId, $message)
    {
        $post = json_encode(array(
            'message' => (int) $message
        ));
        $url = self::$api.'/user/matches/'.$userId;
        $additionalHeaders = array(
            'X-Auth-Token: '.$this->tinderToken,
            'Content-Type: application/json'
        );
        return $this->sendRequest($url, $additionalHeaders, $post);
    }

    /**
     * Updates the user's current location (affects recommendations)
     *
     * @var float $lat  Latitute
     * @var float $lon  Longitude
     * @return object  JOSN response of the server
     */
    public function updateLocation($lat, $lon)
    {
        $post = json_encode(array(
            'lat' => (float) $lat,
            'lon' => (float) $lon
        ));
        $url = self::$api.'/user/ping';
        $additionalHeaders = array(
            'X-Auth-Token: '.$this->tinderToken,
            'Content-Type: application/json'
        );
        return $this->sendRequest($url, $additionalHeaders, $post);
    }

    /**
     * Get updates from Tinder (matches, messages, etc)
     *
     * @return object  JOSN response of the server
     */
    public function getUpdates()
    {
        $url = self::$api.'/updates';
        $additionalHeaders = array('X-Auth-Token: '.$this->tinderToken);
        return $this->sendRequest($url, $additionalHeaders, true);
    }

    /**
     * Like another user
     *
     * @var string $userId  User ID of who to report
     * @return object  JOSN response of the server
     */
    public function like($userId)
    {
        $url = self::$api.'/like/'.$userId;
        $additionalHeaders = array('X-Auth-Token: '.$this->tinderToken);
        return $this->sendRequest($url, $additionalHeaders);
    }

    /**
     * Pass on another user
     *
     * @var string $userId  User ID of who to report
     * @return object  JOSN response of the server
     */
    public function pass($userId)
    {
        $url = self::$api.'/pass/'.$userId;
        $additionalHeaders = array('X-Auth-Token: '.$this->tinderToken);
        return $this->sendRequest($url, $additionalHeaders);

    }
}