<?php

namespace Buuum\Template;


class View
{

    protected $dir;

    /**
     * @var ParseViewInterface
     */
    private $parseView;

    public function __construct($viewpath, ParseViewInterface $parseView)
    {

        $this->dir = $viewpath;
        $this->parseView = $parseView;
    }

    public function getDir()
    {
        return $this->dir;
    }

    public function render($view, array $data = array(), $layout)
    {
        extract($data);

        $view = $this->dir . '/' . $view . '.php';
        if ($layout) {
            $layout = $this->dir . '/' . $layout . '.php';
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