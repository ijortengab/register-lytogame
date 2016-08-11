<?php

namespace IjorTengab;

abstract class AbstractProcessSteps {
    protected $steps;
    protected $current_step;
    /**
     *
     */
    public function __construct()
    {
        $this->steps = $this->steps();
        while ($step = array_shift($this->steps)) {
            $this->current_step = $step;
            if (isset($step['text'])) {
                $this->t($step['text']);
            }
            if (isset($step['input'])) {
                $var_name = $step['input'];
                $var_value = fgets(STDIN);
                $var_value = rtrim($var_value);
                eval($var_name . ' = "' . $var_value.'";');
            }
            if (isset($step['eval'])) {
                $f = $step['eval'];
                eval($f . ';');
            }
            if (isset($step['delay'])) {
                $this->d($step['delay']);
            }
        }
    }
    /**
     *
     */
    protected function t($text)
    {
        echo $text . PHP_EOL;
    }
    /**
     * Delay.
     */
    protected function d($s)
    {
        $s = $s * 1000000;
        usleep($s);
    }
    /**
     *
     */
    abstract public function steps();    
}
