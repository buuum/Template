<?php

namespace Buuum\Template;


interface ParseViewInterface
{
    public function getUrl($name, $options);
    public function getText($text, $params);
    public function getImgPath();
    public function getViewsPath();
    public function getLink($type, $host, $files);
    public function isPageActual($url, $classname);
}