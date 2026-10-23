<?php

namespace Nyumbani\DtTables\Support;

class TableSearch
{
    /**
     * Split a search phrase the way DataTables smart search does.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public static function terms(string $search): array
    {
        //  Match quoted phrases, optionally negated, and plain words
        preg_match_all('/!?["\x{201C}][^"\x{201D}]+["\x{201D}]|[^ ]+/u', mb_strtolower(trim($search)), $matches);

        //  Build the terms
        return array_map(function (string $part) {
            //  A leading ! excludes rows containing the word
            $negative = str_starts_with($part, '!');

            //  Remove the negation and any quotes
            $word = trim(ltrim($part, '!'), "\"\u{201C}\u{201D}");

            //  Return the term
            return ['word' => $word, 'negative' => $negative];
        }, $matches[0]);
    }

    /**
     * Escape the characters that have a meaning inside a LIKE pattern.
     *
     * @author Alvin G. Kaburu <geekaburu@nyumbanitech.co.ke>
     */
    public static function escapeLike(string $value): string
    {
        return str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $value);
    }
}
