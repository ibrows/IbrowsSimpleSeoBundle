<?php
/**
 * Created by iBROWS AG.
 * Date: 19.03.15
 * Time: 14:28
 */

namespace Ibrows\SimpleSeoBundle\Model;


class AliasStringGenerator
{

    protected $order = array();
    protected $maxLength = 100;
    protected $separatorUnique = '-';
    protected $separator = '/';

    public function generateAliasString(array $arguments, AliasExistsInterface $aliasExists  = null)
    {
        $tokens = $this->getTokens($arguments);
        $alias = $this->generate($tokens);
        $this->uniquify($alias,$aliasExists);
        return $alias;

    }

    protected function generate(array $tokens)
    {
        $string = '';
        while ($token = array_pop($tokens)) {
            $token = str_replace($this->separator, '', $token);
            $token = $this->clean($token);
            $string = $token . $this->separator . $string;
        }
        $string =  mb_substr($string, 0, -1);
        $string = mb_substr($string, 0, $this->maxLength);
        return $string;
    }

    protected function getTokens(array $arguments)
    {
        $tokens = array();
        foreach ($arguments as $argument) {
            $argumentTokens = preg_split('/([\s.,!])+/', $argument);
            $tokens = array_merge($tokens, $argumentTokens);
        }
        return array_values(array_filter($tokens));
    }


    protected function clean($string)
    {
        $string = strip_tags(html_entity_decode($string));
        $string = trim($string);
        $string = $this->translit($string);
        $string = strtolower($string);
        $string = preg_replace('~[^-a-z0-9_]+~', '', $string);
        return $string;
    }

    protected function translit($string){
        $stringOutput = (@iconv('UTF-8', 'ASCII//TRANSLIT', $string));
        if($stringOutput === false){
            $string = preg_replace('!([^0-9a-zA-Z\.,:;()/-_\s])!', '', $string); // use more explicit statement, alphanum dont do the job
            $stringOutput = (@iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $string));
            if($stringOutput === false){
                throw new \Exception('Cant convert '. $string);
            }
        }
        return $stringOutput;
    }

    protected function uniquify($alias,AliasExistsInterface $aliasExists  = null)
    {
        $i = 0;
        do {
            $unique_suffix = $this->separatorUnique . $i;
            $newAlias = mb_substr($alias, 0, $this->maxLength - strlen($unique_suffix)) . $unique_suffix;
            $i++;
        } while ($aliasExists != null && $aliasExists->aliasExists($alias));
        return $newAlias;
    }
}