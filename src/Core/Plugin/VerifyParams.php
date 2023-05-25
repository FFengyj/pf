<?php

namespace Pf\System\Core\Plugin;

use Pf\System\Exceptions\Err\VerifyErrException;
use Pf\System\Verify\VerifyFactory;
use Phalcon\Mvc\User\Plugin;

/**
 * Class VerifyParams
 * @package Pf\System\Core\Plugin
 * @author fyj
 */
class VerifyParams extends Plugin
{
    /**
     * @var array
     */
    private $settings = [];

    /**
     * @var array
     */
    protected $err = [];


    /**
     * VerifyParamService constructor.
     * @param array $settings
     */
    public function __construct($settings = [])
    {
        $this->settings = $settings;
    }

    /**
     * @param $data
     * @param bool $check_all
     * @return VerifyErrException[] array
     * @throws VerifyErrException
     */
    public function verify($data, $check_all = false)
    {
        if ($this->settings) {

            foreach ($this->settings as $type => $fields) {
                try {
                    $verify_obj = VerifyFactory::create($type);
                    $verify_obj->verify($data, $fields);
                }catch (VerifyErrException $e) {
                    $this->err[] = $e;
                    if (!$check_all) {
                        throw $e;
                    }
                }
            }
        }
        return $this->err;
    }

    /**
     * @param $settings
     * @return $this
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
        return $this;
    }

    /**
     * @return array
     */
    public function getAllErrors()
    {
        return $this->err;
    }
}