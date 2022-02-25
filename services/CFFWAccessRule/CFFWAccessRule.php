<?php
/**
 * CFFWAccessRule class
 *
 * @author: tuanha
 * @date: 25-Feb-2022
 */
namespace CFBuddy\CFFWAccessRule;

class CFFWAccessRule
{
    /**
     * @var string
     */
    public $target;
    
    /**
     * @var string
     */
    public $value;

    /**
     * @var string
     */
    public $mode;

    /**
     * @var bool
     */
    public $paused;

    /**
     * @var string
     */
    public $note;
    
    /**
     * Instantiate a \CFBuddy\CFFWAccessRule object
     * @param string  $target
     * @param string  $value
     * @param string  $mode
     * @param bool  $paused
     * @param string  $note
     *
     * @return void
     */
    public function __construct(string $target, string $value, string $mode, bool $paused, string $note)
    {
        $this->target = $target;
        $this->value = $value;
        $this->mode = $mode;
        $this->paused = $paused;
        $this->note = $note;
    }
}
