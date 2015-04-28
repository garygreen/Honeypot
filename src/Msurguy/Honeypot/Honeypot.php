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
     * Token attribute
     * 
     * @var string
     */
    protected $tokenAttribute = '';

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
     * 
     * @return string
     */
    public function html()
    {
        // Get honeypot name and token details
        $name = $this->getNameAttribute();
        $tokenAttribute = $this->getTokenAttribute();
        $token = $this->getToken();

        $html = '<div id="' . $name . '_wrap" style="display:none;">' .
                    '<input name="' . $name . '" type="text" value="" id="' . $name . '"/>' .
                    '<input name="' . $tokenAttribute . '" type="text" value="' . $token . '"/>' .
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
     * Get token attribute
     * 
     * @return string
     */
    public function getTokenAttribute()
    {
        return $this->tokenAttribute;
    }

    /**
     * Get speed
     * 
     * @return integer
     */
    public function getSpeed()
    {
        return $this->speed;
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
     * Set token attribute
     * @param string $token
     * @return $this
     */
    public function setTokenAttribute($token)
    {
        $this->tokenAttribute = $token;
        return $this;
    }

    /**
     * Get raw token
     * 
     * @return array
     */
    public function getRawToken()
    {
        return [
            'time' => time(),
            'speed' => $this->getSpeed(),
        ];
    }

    /**
     * Get encrypted token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->encrypter->encrypt(json_encode($this->getRawToken()));
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
    * @param  string $honeypot
    * @return boolean
    */
    public function validateHoneypot($honeypot)
    {
        return $honeypot == '';
    }

    /**
     * Validate honey token
     * 
     * @param  string $token
     * @return boolean
     */
    public function validateToken($token)
    {
        // Decrypt the token
        $token = $this->decryptToken($token);

        // The current time should be greater than the time the form was built + the speed option
        return ( isset($token['time']) && is_numeric($token['time']) && time() > ($token['time'] + $this->speed) );
    }

    /**
     * Determine if honeypot and token is valid
     * 
     * @param  string|null  $honeypot
     * @param  string|null  $token
     * @return boolean
     */
    public function isValid($honeypot = null, $token = null)
    {
        return $this->validateHoneypot($honeypot) && $this->validateToken($token);
    }

    /**
     * Decrypt the given token
     * 
     * @param  mixed $time
     * @return array|null
     */
    public function decryptToken($token)
    {
        if (!is_string($token)) return null;

        try {
            return json_decode($this->encrypter->decrypt($token), true);
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