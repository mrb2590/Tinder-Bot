<?php
/**
 * class.Tinder.php
 * 
 * This software allows automation of user actions on Tinder
 * 
 * @author Mike Buonomo <mrb2590@gmail.com>
 */

require_once dirname(__FILE__).'/class.Tinder.php';

class TinderBot
{
    /**
     * @var Tinder $tinder  An instance of the Tinder class to make
     *         requests to the Tinder API
     */
    private $tinder;

    /**
     * @var array $recs  Stores all reccomendations
     */
    private $recs = array();

    /**
     * Instantiates object
     *
     * @var int $fbId  Facebook ID of the user
     * @var string $fbToken  Facebook access token of the user
     */
    function __construct($fbId, $fbToken)
    {
        $this->tinder = new Tinder($fbId, $fbToken);
    }

    /**
     * Keeps asking Tinder for recommendation until array size is 
     *         greater than or equal to $min 
     *
     * @var int $min  Min number of recommendations to fill
     */
    private function getNRecommendations($min)
    {
        $count = 0;
        while ($count < $min) {
            $response = $this->tinder->getRecommendations();
            foreach ($response->results as $result) {  
                $this->recs[$result->_id] = $result;
            }
            $count = count($this->recs);
        }
    }

    /**
     * Loop through all recommendation and like each one 
     */
    private function likeAllRecommendations()
    {
        foreach ($this->recs as $rec) {
            $response = $this->tinder->like($rec->_id);
            var_dump($response);
        }
    }

    /**
     * RUN THE BOT!!!!!
     */
    public function run()
    {
        $this->getNRecommendations(1);
        $this->likeAllRecommendations();
    }
}