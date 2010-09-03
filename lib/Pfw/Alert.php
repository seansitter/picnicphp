<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * @package       Pfw
 * @author        Sean Sitter <sean@picnicphp.com>
 * @copyright     2010 The Picnic PHP Framework
 * @license       http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @link          http://www.picnicphp.com
 * @since         0.10
 * @filesource
 */

Pfw_Loader::loadClass('Pfw_Session');

/**
 * Provides a programmatic way to add alert errors and notices.
 *
 * This class maintains flash alerts which can be shown to the user in the templates
 * in predefined locations using template helpers {display_alerts}, {display_errors}, 
 * {display_notices}.
 * 
 * @category      Framework
 * @package       Pfw
 */
class Pfw_Alert
{
    const TYPE_ERROR = 'error';
    const TYPE_NOTICE = 'notice';
    
    /**
     * @var string alerts which follow redirects are places in session, 
     * with this session key
     */
    const SESSION_KEY = '_pfw_alerts';
    
    /**
     * @var array contains the alert bundles
     */
    protected static $alerts = array();
    /**
     * @var bool true if the alerts system has been initialized
     */
    protected static $initialized = false;

    /**
     * Initialize alerts alerts.
     * 
     * Initialize alerts alerts by copying redirected alerts from 
     * session to the local <var>$alerts</var> array.
     */
    public static function init()
    {
        // setup alerts
        self::$alerts[self::TYPE_ERROR] = array();
        self::$alerts[self::TYPE_NOTICE] = array();

        if (Pfw_Session::isStarted()) {
            //  setup session alerts
            $session_alerts = Pfw_Session::get(self::SESSION_KEY);
            if (isset($session_alerts)) {
                if (isset($session_alerts[self::TYPE_ERROR])) {
                    self::$alerts[self::TYPE_ERROR] = $session_alerts[self::TYPE_ERROR];
                }
                if (isset($session_alerts[self::TYPE_NOTICE])) {
                    self::$alerts[self::TYPE_NOTICE] = $session_alerts[self::TYPE_NOTICE];
                }
                Pfw_Session::clear(self::SESSION_KEY);
            }
        } else {
            error_log("Pfw_Session is not initialized prior to Pfw_Alert, ".
                "alerts / notices which follow redirects ".
                "may exhibit unexpected behavior", 
            E_USER_WARNING
            );
        }

        self::$initialized = true;
    }


    /**
     * Has alerts system been initialized?
     * 
     * The alerts system needs to be initialzed before it can be used.
     * During the initialization process, alerts stored in the session
     * are copied into the the alerts class.
     * 
     * @see init()
     * @return bool true if alerts have been initialized,
     * false otherwise
     */
    public static function isInitialized()
    {
        return self::$initialized;
    }


    /**
     * Add an error alert which can be shown in the template with the 
     * {display_errors} helper.
     *
     * @param string $message the alert message to add
     * @param bool $follows_redir does this alert follow a redirect?
     */
    public static function addError($message, $follows_redir = false)
    {
        self::add($message, null, $follows_redir, self::TYPE_ERROR);
    }

    /**
     * Adds an alert for a specific form field.
     *
     * @param string $field the form field the message pertains to
     * @param string $message the alert message
     * @param bool $follows_redir does this alert follow a redirect
     */
    public static function addFieldError($field, $message, $follows_redir = false)
    {
        self::add($message, $field, $follows_redir, self::TYPE_ERROR);
    }


    /**
     * Do we have an alert on a specific field?
     *
     * @param string $field
     * @return bool
     */
    public static function hasFieldError($field)
    {
        self::assertInitialized();
        foreach (self::$alerts[self::TYPE_ERROR] as $alert) {
            if ($field == $alert['field']) {
                return true;
            }
        }
        return false;
    }


    /**
     * Do we have any alerts?
     *
     * @return bool
     */
    public static function hasError()
    {
        self::assertInitialized();
        return (empty(self::$alerts[self::TYPE_ERROR])) ? false : true;
    }


    /**
     * Add an notice alert which can be shown in the template with the 
     * {display_notices} helper. 
     *
     * @param string $message the notice message to add
     * @param bool $follows_redir does this notice follow a redirect?
     */
    public static function addNotice($message, $follows_redir = false)
    {
        self::add($message, null, $follows_redir, self::TYPE_NOTICE);
    }


    /**
     * Adds a notice for a specific field.
     *
     * @param string $field the form field the message pertains to
     * @param string $message the notice message
     * @param bool $follows_redir does this notice follow a redirect
     */
    public static function addFieldNotice($field, $message, $follows_redir = false)
    {
        self::add($message, $field, $follows_redir, self::TYPE_NOTICE);
    }


    /**
     * Do we have an notice on a specific field?
     *
     * @param string $field
     * @return bool
     */
    public static function hasFieldNotice($field)
    {
        self::assertInitialized();
        foreach (self::$alerts[self::TYPE_NOTICE] as $alert) {
            if ($field == $alert['field']) {
                return true;
            }
        }
        return false;
    }


    /**
     * Do we have any notice?
     *
     * @return bool
     */
    public static function hasNotice()
    {
        self::assertInitialized();
        return (empty(self::$alerts[self::TYPE_NOTICE])) ? false : true;
    }


    /**
     * Internal error/notice add method.
     * 
     * @param string $message the alert message
     * @param string|null $field the file to add notice on, or null if alert is not on a field 
     * @param bool $follows_redir true if alert must follow redirect, false if immediate page render 
     * @param string $type on of the TYPE_* constants
     */
    protected static function add($message, $field, $follows_redir, $type)
    {
        self::assertInitialized();

        $follows_redir = ($follows_redir) ? true : false;
        $alert = array('message' => $message, 'redir' => $follows_redir, 'field' => $field);
        array_push(self::$alerts[$type], $alert);

        if (true === $follows_redir) {
            if (!Pfw_Session::isStarted()) {
                throw new Pfw_Exception_System(
                  "Session alerts are not available, session has not been initialized"
                  );
            }
             
            $alerts = Pfw_Session::get(self::SESSION_KEY);
            if (empty($alerts)) {
                $alerts = array();
                $alerts[self::TYPE_ERROR] = array();
                $alerts[self::TYPE_NOTICE] = array();
            }
            array_push($alerts[$type], $alert);
            Pfw_Session::set(self::SESSION_KEY, $alerts);
        }
    }


    /**
     * Get all notices for a specific field.
     * 
     * @param string $field
     * @return array all messages
     */
    public static function getFieldNotice($field)
    {
        return self::get(self::TYPE_NOTICE, $field);
    }


    /**
     * Gets all notices, if first argument is true, include field notices.
     *
     * @param bool $include_field_notices
     * @return array of notices
     */
    public static function getNotices($include_field_notices = false)
    {
        return self::get(self::TYPE_NOTICE, null, $include_field_notices);
    }


    /**
     * Get all errors for a specific field.
     * 
     * @param string $field
     * @return array all messages
     */
    public static function getFieldError($field)
    {
        return self::get(self::TYPE_ERROR, $field);
    }


    /**
     * Gets all alerts, include field alerts if first argument is true.
     *
     * @param bool $include_field_alerts
     * @return array collection of alerts
     */
    public static function getErrors($include_field_alerts = false)
    {
        return self::get(self::TYPE_ERROR, null, $include_field_alerts);
    }


    /**
     * Internal get method.
     */
    protected static function get($type, $field = null, $include_fields = false)
    {
        self::assertInitialized();

        $messages = array();
        if ($field !== null) {
            foreach (self::$alerts[$type] as $alert) {
                if ($field == $alert['field']) {
                    array_push($messages, $alert['message']);
                }
            }
            return $messages;
        }

        foreach (self::$alerts[$type] as $alert) {
            if (true == $include_fields) {
                array_push($messages, $alert['message']);
            } elseif (empty($alert['field'])) {
                array_push($messages, $alert['message']);
            }
        }
        return $messages;
    }


    /**
     * Clears all errors. If $field is specificed, clears all errors
     * on a field.
     *
     * @param string $field
     */
    public static function clearErrors($field = null)
    {
        self::clear(self::TYPE_ERROR, $field);
    }


    /**
     * Clears all notices. If $field is specified, clears all notices
     * on a field.
     *
     * @param string $field
     */
    public static function clearNotices($field = null)
    {
        self::clear(self::TYPE_NOTICE, $field);
    }


    /**
     * Clears all alerts (notices and errors).
     */
    public static function clearAll()
    {
        self::clearErrors();
        self::clearNotices();
    }


    /**
     * Internal method to clear all alerts/notices with optional field.
     *
     * @param const $type self::TYPE_ERROR or self::TYPE_NOTICE
     * @param string $field
     */
    protected static function clear($type, $field)
    {
        self::assertInitialized();
        $alerts = Pfw_Session::get(self::SESSION_KEY);

        if (!empty($alerts)) {
            if (null !== field) {
                self::deleteWithFieldFromArray($alerts[$type], $field);
            } else {
                $alerts[$type] = array();
            }
            Pfw_Sesssion::set(self::SESSION_KEY, $alerts);
        }

        if (null !== $field) {
            self::deleteWithFieldFromArray(self::$alerts[$type], $field);
        } else {
            self::$alerts[$type] = array();
        }
    }


    /**
     * Internal helper to clear specific array fields.
     * 
     * @param array $array
     * @param string $field
     */
    protected static function deleteWithFieldFromArray(&$array, $field)
    {
        for ($i = 0; $i < count($array); $i++) {
            if (isset($array[$i]['field']) and ($array[$i]['field'] == $field)) {
                array_splice($array, $i, 1);
            }
        }
    }


    /**
     * Throws an exception if Pfw_Alert is not initialized.
     */
    protected static function assertInitialized()
    {
        if (!self::isInitialized()) {
            throw new Pfw_Exception_System("Cannot use alerts, Pfw_Alerts not initialized");
        }
    }
}
