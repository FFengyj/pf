<?php


namespace Pf\System\Core\Plugin;


class Request extends \Phalcon\Http\Request
{

    protected $attributes = [];


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