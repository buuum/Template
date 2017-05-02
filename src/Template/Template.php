<?php

namespace Buuum\Template;

class Template
{

    private $path;
    private $chars;

    public function __construct($path, $file = false, $chars = false)
    {
        $this->path = $path;
        $this->chars = $chars;

        if (!empty($file) && $file != "false") {
            $this->renderOne($file);
        } else {
            $this->renderAll();
        }
    }

    private function renderOne($file)
    {
        $file = $this->path . '/' . $file . '.php';
        $this->genfile($file);
    }

    private function renderAll()
    {
        $root_ = $this->path . '/*.php';
        $files = $this->rglob($root_);

        foreach ($files as $file) {
            $this->genfile($file);
        }
    }

    private function genfile($file)
    {
        $output = file_get_contents($file);
        if ($this->chars) {

            $output = $this->setCheckboxs($output);
            $output = $this->setOptionSelected($output);
            $output = $this->replace_chars($output);
        } else {
            $output = $this->render($output);
        }
        file_put_contents($file, $output);
    }

    private function render($template)
    {
        return $this->parse($template);
    }

    private function parse($template, $vars = false)
    {
        preg_match('/{{(.*?)}}/s', $template, $m);

        if (!empty($m) && isset($m[0])) {
            $v = $m[0];
            $v2 = $m[1];

            if (strpos($v, '{{URLIMG') !== false) {
                $template = str_replace($v, $this->parseUrlImg($v), $template);
            } elseif (strpos($v, '{{***') !== false) {
                $template = str_replace($v, $this->parseUrlContieneClass($v), $template);
            } elseif (strpos($v, '{{**') !== false) {
                $template = str_replace($v, $this->parseUrlClass($v), $template);
            } elseif (strpos($v, '{{*') !== false) {
                $template = str_replace($v, $this->parseUrl($v), $template);
            } elseif (strpos($v, '{{@@') !== false) {
                $template = str_replace($v, $this->parseIncludeVar($v), $template);
            } elseif (strpos($v, '{{@') !== false) {
                $template = str_replace($v, $this->parseInclude($v), $template);
            } elseif (strpos($v, '{{e.plural') !== false) {
                $template = str_replace($v, $this->parseTextPlural($v), $template);
            } elseif (strpos($v, '{{e.') !== false) {
                $template = str_replace($v, $this->parseText($v), $template);
            } elseif (strpos($v, '{{endif') !== false) {
                $template = $this->str_replace_first($v, '<?php endif; ?>', $template);
            } elseif (strpos($v, '{{elseif') !== false) {
                $part = str_replace(array('{{elseif ', '}}'), array('', ''), $v);
                $template = $this->str_replace_first($v, '<?php elseif(' . $part . '): ?>', $template);
            } elseif (strpos($v, '{{else') !== false) {
                $template = $this->str_replace_first($v, '<?php else: ?>', $template);
            } elseif (strpos($v, '{{if ') !== false) {
                $part = str_replace(array('{{if ', '}}'), array('', ''), $v);
                $template = $this->str_replace_first($v, '<?php if(' . $part . '):?>', $template);
            } elseif (strpos($v, '{{foreach ') !== false) {
                $part = str_replace(array('{{foreach ', '}}'), array('', ''), $v);
                $template = $this->str_replace_first($v, '<?php foreach(' . $part . '):?>', $template);
            } elseif (strpos($v, '{{endforeach') !== false) {
                $template = $this->str_replace_first($v, '<?php endforeach; ?>', $template);
            } elseif (strpos($v, ' or') !== false) {
                $template = $this->str_replace_first($v, $this->parseOr($v), $template);
            } elseif (strpos($v, '=') !== false) {
                $template = $this->str_replace_first($v, "<?php $v2; ?>", $template);
            } else {
                $v2 = $this->applyFilters($v2);
                $template = $this->str_replace_first($v, $v2, $template);
            }

            $template = $this->parse($template, $vars);
        }

        return $template;
    }

    private function str_replace_first($search, $replace, $subject)
    {
        return implode($replace, explode($search, $subject, 2));
    }

    private function parseOr($v)
    {
        $part = str_replace(array('{{', '}}'), array('', ''), $v);
        $parts = explode(' or', $part);
        $parts = array_map('trim', $parts);

        $default = (empty($parts[1])) ? "''" : $parts[1];

        $value = "(isset({$parts[0]}))? $parts[0] : $default";

        return $this->printVar($value);
    }

    private function parseTextPlural($v)
    {
        $part = str_replace(array('{{e.plural', '}}'), array('', ''), $v);
        $parts = explode(':::', $part);

        $plurals = explode('|', $parts[0]);
        array_shift($parts);

        if (!empty($parts)) {

            $parts = array_map('trim', $parts);
            $array_values = [];
            foreach ($parts as $part) {
                $array_values[] = $this->applyFilters($part, false);
            }
            $array = var_export($array_values, true);
            $array = $this->compression($array);
        }

        $count_compare = $parts[0];

        $plurals = array_map('trim', $plurals);
        $re = '@^({(.*)})?.*@';
        $return = '';
        foreach ($plurals as $n => $plural) {
            preg_match($re, $plural, $matches);

            $condition = ($n == 0) ? "if" : "elseif";

            $end = false;
            $num = 0;
            if (!empty($matches[1])) {
                $text = str_replace($matches[1], '', $matches[0]);
                $num = $matches[2];
            } else {
                $text = $matches[0];
                $end = true;
            }

            if ($end) {
                $return .= "<?php else: ?>";
            } else {
                $return .= "<?php $condition($count_compare == $num): ?>";
            }

            if (strpos($text, '%s') !== false) {
                $return .= $this->printVar("\$this->translate(\"$text\", $array)");
            } else {
                $return .= $this->printVar("\$this->translate(\"$text\")");
            }

        }

        $return .= "<?php endif; ?>";

        return $return;

    }

    private function parseText($v)
    {

        $part = str_replace(array('{{e.', '}}'), array('', ''), $v);

        // $text = trim($part);
        $parts = explode(':::', $part);
        $text = addslashes(trim($parts[0]));
        $array = false;
        array_shift($parts);

        if (!empty($parts)) {

            $parts = array_map('trim', $parts);
            $array_values = [];
            foreach ($parts as $part) {
                $array_values[] = $this->applyFilters($part, false);
            }
            $array = var_export($array_values, true);
            $array = $this->compression($array);

        }

        if ($array) {
            $value = "\$this->translate(\"$text\", $array)";
        } else {
            $value = "\$this->translate(\"$text\")";
        }

        return $this->printVar($value);

    }

    private function parseUrl($templates, $print = true)
    {
        $part = str_replace('{{*', '', $templates);
        $part = str_replace('}}', '', $part);

        $part = trim($part);
        $options = false;

        if (strpos($part, ':') !== false) {
            $extras = explode(':', $part);
            $controller = array_shift($extras);
            $name = $controller;

            if (!empty($extras)) {

                $arr = [];
                foreach ($extras as $extra) {
                    $parts = explode('|', $extra);
                    $value = $parts[1];
                    if (substr($parts[1], 0, 1) != '$') {
                        $value = '"' . $value . '"';
                    }
                    $arr['"' . $parts[0] . '"'] = $value;
                }

                $array = var_export($arr, true);
                $array = str_replace("'", "", $array);
                $array = $this->compression($array);

                $options = $array;

            }

        } else {
            $name = $part;
        }


        if ($options) {
            $value = "\$this->getUrl('" . $name . "', $options)";
        } else {
            $value = "\$this->getUrl('" . $name . "')";
        }

        return ($print) ? $this->printVar($value) : $value;

    }

    private function parseUrlClass($templates)
    {

        $part = str_replace('{{**', '', $templates);
        $part = str_replace('}}', '', $part);

        $parts = explode('::', $part);
        $class = 'active';
        if (!empty($parts[1])) {
            $class = $parts[1];
            $part = $parts[0];
        }

        //$url = $this->parseUrl($part, false);

        return $this->printVar("\$this->isPageActual('$part', '$class');");

    }

    private function parseUrlImg($templates)
    {
        //$part = str_replace('{{URLIMG', '', $templates);
        //$part = str_replace('}}', '', $part);

        return $this->printVar("\$this->getImgPath();");

    }

    private function parseUrlContieneClass($templates)
    {
        $part = str_replace('{{***', '', $templates);
        $part = str_replace('}}', '', $part);

        $parts = explode('::', $part);
        $class = 'active';
        if (!empty($parts[1])) {
            $class = $parts[1];
            $part = $parts[0];
        }

        //$url = $this->parseUrl($part, false);

        return $this->printVar("\$this->pageActualStartsWith('$part', '$class');");
    }

    private function parseInclude($include_file)
    {

        $part = str_replace('{{@', '', $include_file);
        $part = str_replace('}}', '', $part);

        if (substr($part, 0, 1) == '/') {
            return '<?php include __DIR__."/' . substr_replace($part, '', 0, 1) . '.php"; ?>';
        } else {
            return '<?php include $' . $part . ';?>';
        }
    }

    private function parseIncludeVar($include_file)
    {

        $part = str_replace('{{@@', '', $include_file);
        $part = str_replace('}}', '', $part);

        return '<?php include __DIR__."/".$' . $part . ';?>';
    }

    private function replace_chars($output)
    {

        $chars = array(
            '&gt;',
            '&lt;',
            '&#39;',
            '&quot;'
        );
        $replaces = array(
            '>',
            '<',
            "'",
            '"'
        );
        return str_replace($chars, $replaces, $output);
    }

    private function rglob($pattern, $flags = 0)
    {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
            $files = array_merge($files, $this->rglob($dir . '/' . basename($pattern), $flags));
        }
        return $files;
    }

    private function compression($buffer)
    {
        $buff = "";
        $buffer = str_replace(array("\r", "\t", '  ', '    ', '    '), '', $buffer);
        $array = explode("\n", $buffer);
        foreach ($array as $num => $arr) {
            if (trim($arr) != '') {
                if (strpos($arr, "'$") !== false) {
                    $arr = str_replace(["'$", "\\'"], ["$", "'"], $arr);
                    $arr = substr($arr, 0, -2) . ',';
                }
                $buff .= $arr;
                $buff .= "\n";
            }
        }
        return preg_replace('/^\s+|\n|\r|\s+$/m', '', $buff);
    }


    public function setCheckboxs($template)
    {
        // {{checked:fo
        $template = preg_replace_callback('/checked:([a-zA-Z0-9]+?)\s/s', array($this, 'setChecked'), $template);
        return $template;
    }

    public function setOptionSelected($template)
    {

        $template = preg_replace_callback('/selected:([a-zA-Z0-9]+?)\s/s', array($this, 'setSelecteds'), $template);
        return $template;
    }

    public function setSelecteds($s)
    {
        return '<?=($' . $s[1] . ')? " selected " : ""?> ';
    }

    public function setChecked($s)
    {
        return '<?=($' . $s[1] . ')? " checked " : ""?> ';
    }

    public function printVar($value)
    {
        return "<?=$value?>";
    }

    protected function applyFilters($var, $print = true)
    {
        $partes = explode('|', $var);
        $variable = $partes[0];
        $filter = (!empty($partes[1])) ? $partes[1] : false;

        if ($filter) {
            $filter_name = $this->getFilterName($filter);
            $filter_value = $this->getFilterValue($filter);
            $variable = "\$this->filter_$filter_name($variable, [$filter_value])";
        }

        return ($print) ? $this->printVar($variable) : $variable;
    }

    protected function getFilterValue($filter)
    {
        if (strpos($filter, "(") !== false) {
            $filter = substr($filter, 0, -1);
            $values = array_slice(explode("(", $filter), 1);
            return $values[0];
        }
        return false;
    }

    protected function getFilterName($filter)
    {
        if (strpos($filter, "(") !== false) {
            $filter = current(array_slice(explode("(", $filter), 0, 1));
        }
        return $filter;
    }

}
