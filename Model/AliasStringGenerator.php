<?php

/**
 * Created by iBROWS AG.
 * Date: 19.03.15
 * Time: 14:28.
 */

namespace Ibrows\SimpleSeoBundle\Model;

/**
 * Class AliasStringGenerator.
 */
/**
 * Class AliasStringGenerator
 * @package Ibrows\SimpleSeoBundle\Model
 */
class AliasStringGenerator
{
    /**
     * @var array
     */
    protected $sortOrder = array();
    /**
     * @var int
     */
    protected $maxLength = 100;
    /**
     * @var string
     */
    protected $separatorUnique = '-';
    /**
     * @var string
     */
    protected $separator = '/';

    /**
     * @var string
     */
    protected $notAllowedCharsPattern = '![^-a-z0-9_]+!';

    /**
     * @param array                $arguments
     * @param AliasExistsInterface $aliasExists
     *
     * @return string
     */
    public function generateAliasString(array $arguments, AliasExistsInterface $aliasExists = null)
    {
        if (count($this->sortOrder)) {
            uksort($arguments, array($this, 'sort'));
        }
        $tokens = $this->getTokens($arguments);
        $alias = $this->generate($tokens);
        $alias = $this->uniquify($alias, $aliasExists);

        return $alias;
    }

    /**
     * @param $a
     * @param $b
     * @return int
     */
    protected function sort($a, $b)
    {
        $resulta = array_search($a, $this->sortOrder);
        $resultb = array_search($b, $this->sortOrder);
        if ($resulta === $resultb) {
            return 0;
        }
        if ($resulta === false || ($resultb !== false && $resulta > $resultb)) {
            return 1;
        }

        return -1;
    }

    /**
     * @param array $tokens
     *
     * @return string
     */
    protected function generate(array $tokens)
    {
        $string = '';
        while ($token = array_pop($tokens)) {
            $token = str_replace($this->separator, '', $token);
            if ($token == '') {
                continue;
            }
            $token = $this->clean($token);
            $string = $token.$this->separator.$string;
        }
        $string = mb_substr($string, 0, -1);
        $string = mb_substr($string, 0, $this->maxLength);

        return $string;
    }

    /**
     * @param array $arguments
     *
     * @return array
     */
    protected function getTokens(array $arguments)
    {
        $tokens = array();
        foreach ($arguments as $argument) {
            $argumentTokens = preg_split('/([\s.,!])+/', $argument);
            $tokens = array_merge($tokens, $argumentTokens);
        }

        return array_values(array_filter($tokens));
    }

    /**
     * @param $string
     *
     * @return string
     *
     * @throws \Exception
     */
    protected function clean($string)
    {
        $string = strip_tags(html_entity_decode($string));
        $string = trim($string);
        $string = $this->translit($string);
        $string = strtolower($string);
        $string = preg_replace($this->getNotAllowedCharsPattern(), '', $string);

        return $string;
    }

    /**
     * @param $string
     *
     * @return string
     *
     * @throws \Exception
     */
    protected function translit($string)
    {
        $stringOutput = (@iconv('UTF-8', 'ASCII//TRANSLIT', $string));
        if ($stringOutput === false) {
            $string = preg_replace('!([^0-9a-zA-Z\.,:;()/-_\s])!', '', $string); // use more explicit statement, alphanum dont do the job
            $stringOutput = (@iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $string));
            if ($stringOutput === false) {
                throw new \Exception('Cant convert '.$string);
            }
        }

        return $stringOutput;
    }

    /**
     * @param string               $alias
     * @param AliasExistsInterface $aliasExists
     *
     * @return string
     */
    protected function uniquify($alias, AliasExistsInterface $aliasExists = null)
    {
        $i = 0;
        $newAlias = $alias;
        while ($aliasExists != null && $aliasExists->aliasExists($newAlias)) {
            $unique_suffix = $this->separatorUnique.$i;
            $newAlias = mb_substr($alias, 0, $this->maxLength - strlen($unique_suffix)).$unique_suffix;
            ++$i;
        }

        return $newAlias;
    }

    /**
     * @return int
     */
    public function getMaxLength()
    {
        return $this->maxLength;
    }

    /**
     * @param int $maxLength
     */
    public function setMaxLength($maxLength)
    {
        $this->maxLength = $maxLength;
    }

    /**
     * @return string
     */
    public function getSeparatorUnique()
    {
        return $this->separatorUnique;
    }

    /**
     * @param string $separatorUnique
     */
    public function setSeparatorUnique($separatorUnique)
    {
        $this->separatorUnique = $separatorUnique;
    }

    /**
     * @return string
     */
    public function getSeparator()
    {
        return $this->separator;
    }

    /**
     * @param string $separator
     */
    public function setSeparator($separator)
    {
        $this->separator = $separator;
    }

    /**
     * @return array
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * @param array $sortOrder
     */
    public function setSortOrder(array $sortOrder)
    {
        $this->sortOrder = $sortOrder;
    }

    /**
     * @return string
     */
    public function getNotAllowedCharsPattern()
    {
        return $this->notAllowedCharsPattern;
    }

    /**
     * @param string $notAllowedCharsPattern
     */
    public function setNotAllowedCharsPattern($notAllowedCharsPattern)
    {
        $this->notAllowedCharsPattern = $notAllowedCharsPattern;
    }


}
