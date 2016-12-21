<?php

/**
 * Dizkus
 *
 * @copyright (c) 2001-now, Dizkus Development Team
 * @see https://github.com/zikula-modules/Dizkus
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Dizkus
 */

namespace Zikula\DizkusModule\Api;

use DataUtil;

/**
 * This class provides the api functions for rudimentary parsing of
 * bracket-tag code for [quote] and [code]
 */
class ParseTagsApi extends \Zikula_AbstractApi
{
    /**
     * transform only [quote] and [code] tags
     */
    public function transform($args)
    {
        $message = $args['message'];

        // pad it with a space so we can distinguish between FALSE and matching the 1st char (index 0).
        // This is important; encode_quote() and encode_code() depend on it.
        $message = ' ' . $message . ' ';

        // If there is a "[" and a "]" in the message, process it.
        if ((strpos($message, "[") && strpos($message, "]"))) {
            // [CODE] and [/CODE] for posting code (HTML, PHP, C etc etc) in your posts.
            $message = $this->encode_code($message);

            // [QUOTE] and [/QUOTE] for posting replies with quote, or just for quoting stuff.
            $message = $this->encode_quote($message);
        }

        // Remove added padding from the string..
        $message = substr($message, 1);
        $message = substr($message, 0, -1);

        return $message;
    }

    /**
     * Nathan Codding - Jan. 12, 2001.
     * modified again in 2013 when inserted into Dizkus
     * Performs [quote][/quote] encoding on the given string, and returns the results.
     * Any unmatched "[quote]" or "[/quote]" token will just be left alone.
     * This works fine with both having more than one quote in a message, and with nested quotes.
     * Since that is not a regular language, this is actually a PDA and uses a stack. Great fun.
     *
     * Note: This function assumes the first character of $message is a space, which is added by
     * transform().
     */
    private function encode_quote($message)
    {
        // First things first: If there aren't any "[quote=" or "[quote] strings in the message, we don't
        // need to process it at all.
        if (!strpos(strtolower($message), "[quote=") && !strpos(strtolower($message), "[quote]")) {
            return $message;
        }

        $quotebody = '
<div class="dz-quote">
    <i class="fa fa-quote-left fa-4x text-more-muted"></i>
    <div class="inner">
        <div class="dz-quoteheader">%u</div>
        <blockquote class="dz-quotetext">%t</blockquote>
    </div>
    <i class="fa fa-quote-right fa-4x text-more-muted"></i>
</div>';

        $stack = array();
        $curr_pos = 1;
        while ($curr_pos && ($curr_pos < strlen($message))) {
            $curr_pos = strpos($message, "[", $curr_pos);

            // If not found, $curr_pos will be 0, and the loop will end.
            if ($curr_pos) {
                // We found a [. It starts at $curr_pos.
                // check if it's a starting or ending quote tag.
                $possible_start = substr($message, $curr_pos, 6);
                $possible_end_pos = strpos($message, "]", $curr_pos);
                $possible_end = substr($message, $curr_pos, $possible_end_pos - $curr_pos + 1);
                if (strcasecmp("[quote", $possible_start) == 0) {
                    // We have a starting quote tag.
                    // Push its position on to the stack, and then keep going to the right.
                    array_push($stack, $curr_pos);
                    ++$curr_pos;
                } elseif (strcasecmp("[/quote]", $possible_end) == 0) {
                    // We have an ending quote tag.
                    // Check if we've already found a matching starting tag.
                    if (count($stack) > 0) {
                        // There exists a starting tag.
                        // We need to do 2 replacements now.
                        $start_index = array_pop($stack);

                        // everything before the [quote=xxx] tag.
                        $before_start_tag = substr($message, 0, $start_index);

                        // find the end of the start tag
                        $start_tag_end = strpos($message, "]", $start_index);
                        $start_tag_len = $start_tag_end - $start_index + 1;
                        if ($start_tag_len > 7) {
                            $username = DataUtil::formatForDisplay(substr($message, $start_index + 7, $start_tag_len - 8));
                        } else {
                            $username = DataUtil::formatForDisplay($this->__('Quote'));
                        }

                        // everything after the [quote=xxx] tag, but before the [/quote] tag.
                        $between_tags = substr($message, $start_index + $start_tag_len, $curr_pos - ($start_index + $start_tag_len));
                        // everything after the [/quote] tag.
                        $after_end_tag = substr($message, $curr_pos + 8);

                        $quotetext = str_replace('%u', $username, $quotebody);
                        $quotetext = str_replace('%t', $between_tags, $quotetext);

                        $message = $before_start_tag . $quotetext . $after_end_tag;

                        // Now.. we've screwed up the indices by changing the length of the string.
                        // So, if there's anything in the stack, we want to resume searching just after it.
                        // otherwise, we go back to the start.
                        if (count($stack) > 0) {
                            $curr_pos = array_pop($stack);
                            array_push($stack, $curr_pos);
                            ++$curr_pos;
                        } else {
                            $curr_pos = 1;
                        }
                    } else {
                        // No matching start tag found. Increment pos, keep going.
                        ++$curr_pos;
                    }
                } else {
                    // No starting tag or ending tag.. Increment pos, keep looping.,
                    ++$curr_pos;
                }
            }
        } // while

        return $message;
    }

    /**
     * Nathan Codding - Jan. 12, 2001.
     * Frank Schummertz - Sept. 2004ff
     * modified again in 2013 when inserted into Dizkus
     * Performs [code][/code] transformation on the given string, and returns the results.
     * Any unmatched "[code]" or "[/code]" token will just be left alone.
     * This works fine with both having more than one code block in a message, and with nested code blocks.
     * Since that is not a regular language, this is actually a PDA and uses a stack. Great fun.
     *
     * Note: This function assumes the first character of $message is a space, which is added by
     * transform().
     */
    private function encode_code($message)
    {
        $count = preg_match_all("#(\[code=*(.*?)\])(.*?)(\[\/code\])#si", $message, $code);
        // with $message="[code=php,start=25]php code();[/code]" the array $code now contains
        // [0] [code=php,start=25]php code();[/code]
        // [1] [code=php,start=25]
        // [2] php,start=25
        // [3] php code();
        // [4] [/code]

        if ($count > 0 && is_array($code)) {
            $codebodyblock ='<!--code--><div class="dz-code"><div class="dz-codeheader">%h</div><div class="dz-codetext">%c</div></div><!--/code-->';
            $codebodyinline ='<!--code--><code>%c</code><!--/code-->';

            for ($i = 0; $i < $count; $i++) {
                // the code in between incl. code tags
                $str_to_match = "/" . preg_quote($code[0][$i], "/") . "/";

                $after_replace = trim($code[3][$i]);
                $containsLineEndings = !strstr($after_replace, PHP_EOL) ? false : true;

                if ($containsLineEndings) {
                    $after_replace = '<pre class="pre-scrollable">' . DataUtil::formatForDisplay($after_replace) . '</pre>';
                    // replace %h with 'Code'
                    $codetext = str_replace("%h", $this->__('Code'), $codebodyblock);
                    // replace %c with code
                    $codetext = str_replace("%c", $after_replace, $codetext);
                    // replace %e with urlencoded code (prepared for javascript)
                    $codetext = str_replace("%e", urlencode(nl2br($after_replace)), $codetext);
                } else {
                    $after_replace = DataUtil::formatForDisplay($after_replace);
                    // replace %c with code
                    $codetext = str_replace("%c", $after_replace, $codebodyinline);
                    // replace %e with urlencoded code (prepared for javascript)
                    $codetext = str_replace("%e", urlencode(nl2br($after_replace)), $codetext);
                }

                $message = preg_replace($str_to_match, $codetext, $message);
            }
        }

        return $message;
    }
}
