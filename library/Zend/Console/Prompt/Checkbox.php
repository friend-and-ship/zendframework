<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Zend\Console\Prompt;

use Zend\Console\Exception;
use Zend\Stdlib\ArrayUtils;

final class Checkbox extends AbstractPrompt
{
    /**
     * @var string
     */
    private $promptText = 'Please select an option (Enter to finish) ';

    /**
     * @var bool
     */
    private $ignoreCase = true;

    /**
     * @var array|Transversable
     */
    private $options = array();

    /**
     * Checked options
     * @var array
     */
    private $checkedOptions = array();

    /**
     * If the response should be echoed to the console or not
     * @var bool
     */
    private $echo = false;

    /**
     * Ask the user to select any number of pre-defined options
     *
     * @param string                $promptText     The prompt text to display in console
     * @param array|Transversable   $options        Allowed options
     * @param bool                  $echo           True to display selected option?
     */
    public function __construct($promptText = 'Please select one option (Enter to finish) ', $options = array(), $ignoreCase = true, $echo = false)
    {
        $this->promptText = (string)$promptText;

        $this->setOptions($options);

        $this->echo = (bool)$echo;

        $this->ignoreCase = (bool)$ignoreCase;
    }

    /**
     * Show a list of options and prompt the user to select any number of them.
     *
     * @return array Checked options
     */
    public function show()
    {
        $console = $this->getConsole();
        $this->checkedOptions = array();
        $mask = $this->prepareMask();

        do {
            $this->showAvailableOptions();

            $response = $this->readOption($mask);

            if ($this->echo) {
                $this->showResponse();
            }

            $this->checkOrUncheckOption($response);
        } while ($response != "\r" && $response != "\n");

        $this->lastResponse = $this->checkedOptions;
        return $this->checkedOptions;
    }

    /**
     * Shows the selected option to the screen
     * @param string $response
     */
    private function showResponse($response)
    {
        $console = $this->getConsole();
        if (isset($this->options[$response])) {
            $console->writeLine($this->options[$response]);
        } else {
            $console->writeLine();
        }
    }

    /**
     * Check or uncheck an option
     *
     * @param string $response
     */
    private function checkOrUncheckOption($response)
    {
        if ($response != "\r" && $response != "\n" && isset($this->options[$response])) {
            $pos = array_search($this->options[$response], $this->checkedOptions);
            if ($pos === false) {
                $this->checkedOptions[] = $this->options[$response];
            } else {
                array_splice($this->checkedOptions, $pos, 1);
            }
        }
    }

    /**
     * Generates a mask to to be used by the readChar method.
     *
     * @return string
     */
    private function prepareMask()
    {
        $mask = implode("", array_keys($this->options));
        $mask .= "\r\n";

        /**
         * Normalize the mask if case is irrelevant
         */
        if ($this->ignoreCase) {
            $mask = strtolower($mask); // lowercase all
            $mask .= strtoupper($mask); // uppercase and append
            $mask = str_split($mask); // convert to array
            $mask = array_unique($mask); // remove duplicates
            $mask = implode("", $mask); // convert back to string
        }

        return $mask;
    }

    /**
     * Reads a char from console.
     *
     * @param string $mask
     * @return string
     */
    private function readOption($mask)
    {
        /**
         * Read char from console
         */
        $char = $this->getConsole()->readChar($mask);

        return $char;
    }

    /**
     * Shows the available options with checked and unchecked states
     */
    private function showAvailableOptions()
    {
        $console = $this->getConsole();
        $console->writeLine($this->getPromptText());
        foreach ($this->options as $k => $v) {
            $console->writeLine('  ' . $k . ') ' . (in_array($v, $this->checkedOptions) ? '[X] ' : '[ ] ') . $v);
        }
    }

    /**
     * Set allowed options
     *
     * @param array|\Traversable $options
     * @throws Exception\InvalidArgumentException
     */
    private function setOptions($options)
    {
        $options = ArrayUtils::iteratorToArray($options);

        if (empty($options)) {
            throw new Exception\InvalidArgumentException('Please, specify at least one option');
        }

        $this->options = $options;
    }

    /**
     * @return array
     */
    private function getOptions()
    {
        return $this->options;
    }
}
