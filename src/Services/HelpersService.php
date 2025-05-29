<?php

namespace Amprest\DtTables\Services;

use Illuminate\Support\Arr;
use Illuminate\View\ComponentAttributeBag;

class HelpersService
{
    /**
     * Convert an array of attributes to HTML.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function parseAttributes(array $attributes): string
    {
        return (new ComponentAttributeBag($attributes))->toHtml();
    }

    /**
     * Parse the modal content to remove new lines and spaces.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function renderTemplate(string $view, array $params = []): string
    {
        //  Render the view
        $content = view($view, $params)->render();

        //  Remove new lines and spaces
        $content = preg_replace('/>\s+</', '><', $content);

        //  Collapse any other spaces
        $content = preg_replace('/\s+/', ' ', $content);

        //  Trim the content
        return trim($content);
    }

    /**
     * Convert an array of classes to a CSS class string.
     *
     * @author Alvin G. Kaburu <geekaburu@amprest.co.ke>
     */
    public function columnClasses(array $classes): string
    {
        return Arr::toCssClasses($classes);
    }
}
