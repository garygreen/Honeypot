<?php namespace Msurguy\Honeypot;

use Exception;
use Illuminate\Encryption\Encrypter;
use Illuminate\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\DecryptException as DecryptExceptionContract;

class Honeypot {

    /**
     * Encrypter
     * 
     * @var Illuminate\Encryption\Encrypter
     */
    protected $encrypter;

    /**
     * How long the form must have taken in seconds
     * 
     * @var integer
     */
    protected $speed = 5;

    /**
     * Name attribute
     * 
     * @var string
     */
    protected $nameAttribute = '';

    /**
     * Time attribute
     * 
     * @var string
     */
    protected $timeAttribute = '';

    /**
     * Start honeypot
     *
     * @param Illuminate\Encryption\Encrypter $encrypter
     */
    public function __construct(Encrypter $encrypter)
    {
        $this->encrypter = $encrypter;
    }

    /**
     * Get the honey pot form HTML
     * @return string
     */
    public function html()
    {
        // Encrypt the current time
        $encryptedTime = $this->encrypter->encrypt(time());

        // Get honeypot name and time attributes
        $name = $this->getNameAttribute();
        $time = $this->getTimeAttribute();

        $html = '<div id="' . $name . '_wrap" style="display:none;">' . "\r\n" .
                    '<input name="' . $name . '" type="text" value="" id="' . $name . '"/>' . "\r\n" .
                    '<input name="' . $time . '" type="text" value="' . $encryptedTime . '"/>' . "\r\n" .
                '</div>';

        return $html;
    }

    /**
     * Get name attribute
     *
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->nameAttribute;
    }

    /**
     * Get time attribute
     * 
     * @return string
     */
    public function getTimeAttribute()
    {
        return $this->timeAttribute;
    }

    /**
     * Set name attribute
     * @param string $name
     * @return $this
     */
    public function setNameAttribute($name)
    {
        $this->nameAttribute = $name;
        return $this;
    }

    /**
     * Set time attribute
     * @param string $time
     * @return $this
     */
    public function setTimeAttribute($time)
    {
        $this->timeAttribute = $time;
        return $this;
    }

    /**
     * Set how long the form must have taken in seconds
     * 
     * @param  integer $secs
     * @return $this
     */
    public function speed($secs)
    {
        $this->speed = $secs;
        return $this;
    }

    /**
    * Validate honeypot is empty
    * 
    * @param  mixed $honeypot
    * @return boolean
    */
    public function validateHoneypot($honeypot)
    {
        return $honeypot == '';
    }

    /**
     * Validate honey time was within the time limit
     * 
     * @param  mixed $time
     * @return boolean
     */
    public function validateHoneytime($time)
    {
        // Get the decrypted time
        $time = $this->decryptTime($time);

        // The current time should be greater than the time the form was built + the speed option
        return ( is_numeric($time) && time() > ($time + $this->speed) );
    }

    /**
     * Determine if honeypot and time is valid
     * 
     * @param  string|null  $honeypot
     * @param  string|null  $time
     * @return boolean
     */
    public function isValid($honeypot = null, $time = null)
    {
        return $this->validateHoneypot($honeypot) && $this->validateHoneytime($time);
    }

    /**
     * Decrypt the given time
     * 
     * @param  mixed $time
     * @return string|null
     */
    public function decryptTime($time)
    {
        try {
            return $this->encrypter->decrypt($time);
        }
        catch (Exception $e)
        {
            if ($e instanceof DecryptException || $e instanceof DecryptExceptionContract)
            {
                return null;
            }

            throw $e;
        }
    }

}