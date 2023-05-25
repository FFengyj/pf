<?php


namespace Pf\System\Core\Plugin;


class Response extends \Phalcon\Http\Response
{

    protected $attributes = [];

    public function __construct($content = null, $code = null, $status = null)
    {
        parent::__construct($content, $code, $status);
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function setAttribute($name, $value)
    {
        $this->attributes[$name] = $value;
        return $this;
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function getAttribute($name)
    {
        return $this->attributes[$name] ?? null;
    }
}