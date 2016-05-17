<?php

namespace Buuum\Template;

class Header
{

    private $host;
    private $scope;
    protected $index = true;
    protected $feed = true;
    protected $favicon = '/favicon.ico';
    protected $feedurl = '/feed/';
    protected $title;
    protected $description;
    protected $keywords;
    protected $canonical = false;
    protected $plugins = array();
    protected $cssjsversion;

    public function __construct($host, $scope, $cssjsversion = false)
    {
        $this->host = $host;
        $this->cssjsversion = $cssjsversion;
        $this->scope = $scope;
    }

    public function get()
    {

        if ($this->favicon == '/favicon.ico') {
            $this->favicon = '//' . $this->host . '/favicon.ico';
        }
        if ($this->feedurl == '/feed/') {
            $this->feedurl = '//' . $this->host . '/feed/';
        }

        $array = array(
            'index'         => $this->index,
            'feed'          => $this->feed,
            'feedurl'       => $this->feedurl,
            'title'         => $this->title,
            'favicon'       => $this->favicon,
            'description'   => $this->description,
            'keywords'      => $this->keywords,
            'canonical'     => $this->canonical,
            'pluginscssurl' => $this->pluginsurl('css'),
            'pluginsjsurl'  => $this->pluginsurl('js')
        );

        return $array;
    }

    public function pluginsurl($tipo = 'css')
    {
        $files = base64_encode(implode(',', $this->plugins));
        $version = ($this->cssjsversion)? $this->cssjsversion : 0;
        return '//' . $this->host . '/assets/' . $tipo . '.php?f=' . $this->scope . '&p=' . $files . '&pre=' . $version;
    }

    public function index($value)
    {
        $this->index = $value;
        return $this;
    }

    public function feed($value)
    {
        $this->feed = $value;
        return $this;
    }

    public function feedurl($value)
    {
        $this->feedurl = $value;
        return $this;
    }

    public function title($value)
    {
        $this->title = $value;
        return $this;
    }

    public function description($value)
    {
        $this->description = $value;
        return $this;
    }

    public function keywords($value)
    {
        $this->keywords = $value;
        return $this;
    }

    public function canonical($value)
    {
        $this->canonical = $value;
        return $this;
    }

    public function favicon($value)
    {
        $this->favicon = $value;
        return $this;
    }

    public function plugins($value = array())
    {
        if (!empty($value) && is_array($value)) {
            $arrs = [];
            foreach ($value as $val) {
                $arrs[] = $val;
            }
            $this->plugins = array_merge($this->plugins, $arrs);
        }
        return $this;
    }
}