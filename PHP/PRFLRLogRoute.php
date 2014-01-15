<?php

/**
 * @brief PRFLR log router for Yii Framework
 * @note following options should be enabled in config
 *     Yii::getLogger()->autoFlush = 1;
 *     Yii::getLogger()->autoDump = 1;
 * 
 * @author     Alexey Spiridonov <forspall@gmail.com>
 */
class PRFLRLogRoute extends CLogRoute {

    public $source;
    public $apikey;

    /**
     * @brief init log router
     */
    public function init() {
        parent::init();
        include_once(__DIR__ . '/prflr.php');
        PRFLR::init($this->source, $this->apikey);
    }

    /**
     * Stores log messages 
     * @param array $logs list of log messages
     */
    protected function processLogs($logs) {
        // work only with first element
        $_log = $logs[0];
        // profile log message have begin: and end: prefixes
        if ($_log[1] == 'profile') {
            if ($_log[0][0] == 'b') {
                // begin
                PRFLR::Begin(substr($_log[0], 6, strlen($_log[0])));
            } else {
                //end
                PRFLR::End(substr($_log[0], 4, strlen($_log[0])), $_log[2]);
            }
        }
    }

}
