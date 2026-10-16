<?php

namespace Amprest\DtTables\Views\Components\Docs;

use Illuminate\Support\Str;
use Illuminate\View\Component;
use Illuminate\View\View;

class Code extends Component
{
    /**
     * The id of the code element, which the copy button points at.
     */
    public string $id;

    /**
     * Create a code block for a language, with an optional title such as the file it belongs to.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function __construct(public string $lang = 'php', public ?string $title = null)
    {
        $this->id = 'code-'.Str::random(8);
    }

    /**
     * Get the name of the language as the reader sees it.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function label(): string
    {
        return match ($this->lang) {
            'blade' => 'Blade',
            'json' => 'JSON',
            'bash' => 'Terminal',
            'js' => 'JavaScript',
            default => strtoupper($this->lang),
        };
    }

    /**
     * Get the name of the highlighter that colours the language, where Blade is coloured as markup.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function highlighter(): string
    {
        return match ($this->lang) {
            'blade' => 'xml',
            'js' => 'javascript',
            default => $this->lang,
        };
    }

    /**
     * Strip the indentation the snippet inherits from the page it is written in, and restore an opening PHP tag written as an entity, since Blade would run a literal one.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function dedent(string $code): string
    {
        //  Restore the opening PHP tags that were written as entities
        $code = str_replace('&lt;?php', '<?php', $code);

        //  Split the code into lines, dropping the blank ones around it
        $lines = explode("\n", trim($code, "\n\r"));

        //  Find the smallest indentation among the lines that have text
        $indent = collect($lines)
            ->filter(fn (string $line) => trim($line) !== '')
            ->map(fn (string $line) => strlen($line) - strlen(ltrim($line)))
            ->min() ?? 0;

        //  Remove it from every line
        return implode("\n", array_map(fn (string $line) => substr($line, $indent), $lines));
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public function render(): View
    {
        return view('dt-tables::components.docs.code');
    }
}
