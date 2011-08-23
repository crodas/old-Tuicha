<?php
/**
 * ----------------------------------------------------------------------------
 * Tuicha.
 *
 * <crodas@php.net> wrote this file. As long as you retain this notice you
 * can do whatever you want with this stuff. If we meet some day, and you think
 * this stuff is worth it, you can buy me a beer in return to César Rodas.
 * ----------------------------------------------------------------------------
 */
namespace Tuicha;

class Event
{
    protected $listeners;

    public function bind($name, $listener) 
    {
        if (!is_callable($listener)) {
            throw new \RuntimeException("callback is not callable");
        }
        if (!isset($this->listeners[$name])) {
            $this->listeners[$name] = array();
        }
        $this->listeners[$name][] = $listener;

        return $this;
    }

    public function unbind($name, $listener) 
    {
        if (!isset($this->listeners[$name])) {
            return false;
        }
        $id = array_search($listener, $this->listeners[$name], true);
        if ($id !== false) {
            unset($this->listeners[$name][$id]);
        }

        return $id !== false;
    }

    public function unbindAll($name) 
    {
        if (isset($this->listeners[$name]) && count($this->listeners[$name]) > 0) {
            unset($this->listeners[$name]);
            return true;
        }
        return false;
    }

    public function trigger($name, $params = array())
    {
        if (!isset($this->listeners[$name])) {
            return;
        }
        foreach($this->listeners[$name] as $event) {
            $return = call_user_func($event, $params);
            if ($return === false) {
                break;
            }
        }
    }

}
