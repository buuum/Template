<?php

namespace Buuum\Template;


class View
{
    /**
     * @var ParseViewInterface
     */
    private $parseView;

    /**
     * @var Header
     */
    public $header;

    public function __construct($host, ParseViewInterface $parseView)
    {
        $this->parseView = $parseView;
        $this->header = new Header($host);
    }

    public function getHeader($var)
    {
        return $this->header->get($var);
    }

    public function getLink($type)
    {
        return $this->parseView->getLink($type, $this->header->getHost(), $this->header->getPlugins());
    }

    public function render($view, $data = null, $layout)
    {
        if ($data) {
            if (is_object($data)) {
                $data = (array)$data;
            }
            extract($data);
        }

        $dir = $this->parseView->getViewsPath();

        $view = $dir . '/' . $view . '.php';
        if ($layout) {
            $layout = $dir . '/' . $layout . '.php';
        } else {
            $layout = $view;
        }

        ob_start();
        include $layout;
        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->parseView, $name), $arguments);
    }

}