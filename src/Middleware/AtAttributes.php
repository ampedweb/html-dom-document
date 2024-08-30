<?php

namespace Future\HTMLDocument\Middleware;

/**
 * Ensure attributes on elements starting with @ don't get stripped out.
 */
class AtAttributes extends AbstractMiddleware
{
    protected string $prefixToReplace = '@';
    protected string $placeholder = 'x-on:';

    public function beforeLoadHTML(string $source): string
    {
        $pattern = '/(' . $this->prefixToReplace . '[\w.-]+)=/';

        return (string) preg_replace_callback($pattern, function ($matches) {
            return str_replace($this->prefixToReplace, $this->placeholder, $matches[0]);
        }, $source);
    }

    public function afterSaveHTML(string $source): string
    {
        return (string) preg_replace_callback('/('. $this->placeholder .'[\w.-]+)=/', function ($matches) {
            return str_replace($this->placeholder, $this->prefixToReplace, $matches[0]);
        }, $source);
    }
}
