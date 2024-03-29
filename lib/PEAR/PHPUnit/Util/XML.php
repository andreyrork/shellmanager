<?php
/**
 * PHPUnit
 *
 * Copyright (c) 2002-2008, Sebastian Bergmann <sb@sebastian-bergmann.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2008 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    SVN: $Id: XML.php 3773 2008-09-09 10:17:53Z sb $
 * @link       http://www.phpunit.de/
 * @since      File available since Release 3.2.0
 */

require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__, 'PHPUNIT');

/**
 * XML helpers.
 *
 * @category   Testing
 * @package    PHPUnit
 * @author     Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @copyright  2002-2008 Sebastian Bergmann <sb@sebastian-bergmann.de>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version    Release: 3.3.1
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 3.2.0
 */
class PHPUnit_Util_XML
{
    /**
     * Converts a string to UTF-8 encoding.
     *
     * @param  string $string
     * @return string
     * @since  Method available since Release 3.2.19
     */
    public static function convertToUtf8($string)
    {
        if (!self::isUtf8($string)) {
            if (function_exists('mb_convert_encoding')) {
                $string = mb_convert_encoding($string, 'UTF-8');
            } else {
                $string = utf8_encode($string);
            }
        }

        return $string;
    }

    /**
     * Checks a string for UTF-8 encoding.
     *
     * @param  string $string
     * @return boolean
     * @since  Method available since Release 3.3.0
     */
    public static function isUtf8($string)
    {
        // See http://www.w3.org/International/questions/qa-forms-utf-8.en.php
        return preg_match(
            '%^(?:
            [\x09\x0A\x0D\x20-\x7E]            # ASCII
          | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
          |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
          | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
          |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
          |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
          | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
          |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
          )*$%xs',
          $string
        );
    }

    /**
     * Loads an XML (or HTML) file into a DOMDocument object.
     *
     * @param  string  $filename
     * @param  boolean $isHtml
     * @return DOMDocument
     * @since  Method available since Release 3.3.0
     */
    public static function loadFile($filename, $isHtml = FALSE)
    {
        $reporting = error_reporting(0);
        $contents  = file_get_contents($filename);
        error_reporting($reporting);

        if ($contents === FALSE) {
            throw new RuntimeException(
              sprintf(
                'Could not read "%s".',
                $filename
              )
            );
        }

        return self::load($contents, $isHtml, $filename);
    }

    /**
     * Load an $actual document into a DOMDocument.  This is called
     * from the selector assertions.
     *
     * If $actual is already a DOMDocument, it is returned with
     * no changes.  Otherwise, $actual is loaded into a new DOMDocument
     * as either HTML or XML, depending on the value of $isHtml.
     *
     * Note: prior to PHPUnit 3.3.0, this method loaded a file and
     * not a string as it currently does.  To load a file into a
     * DOMDocument, use loadFile() instead.
     *
     * @param  string|DOMDocument  $actual
     * @param  boolean             $isHtml
     * @param  string              $filename
     * @return DOMDocument
     * @since  Method available since Release 3.3.0
     * @author Mike Naberezny <mike@maintainable.com>
     * @author Derek DeVries <derek@maintainable.com>
     */
    public static function load($actual, $isHtml = FALSE, $filename = '')
    {
        if ($actual instanceof DOMDocument) {
            return $actual;
        }

        $internal  = libxml_use_internal_errors(TRUE);
        $reporting = error_reporting(0);
        $dom       = new DOMDocument;

        if ($isHtml) {
            $loaded = $dom->loadHTML($actual);
        } else {
            $loaded = $dom->loadXML($actual);
        }

        libxml_use_internal_errors($internal);
        error_reporting($reporting);

        if ($loaded === FALSE) {
            $message = '';

            foreach (libxml_get_errors() as $error) {
                $message .= $error->message;
            }

            if ($filename != '') {
                throw new RuntimeException(
                  sprintf(
                    'Could not load "%s".%s',

                    $filename,
                    $message != '' ? "\n" . $message : ''
                  )
                );
            } else {
                throw new RuntimeException($message);
            }
        }

        return $dom;
    }

    /**
     *
     *
     * @param  DOMNode $node
     * @since  Method available since Release 3.3.0
     * @author Mattis Stordalen Flister <mattis@xait.no>
     */
    public static function removeCharacterDataNodes(DOMNode $node)
    {
        if ($node->hasChildNodes()) {
            for ($i = $node->childNodes->length - 1; $i >= 0; $i--) {
                if (($child = $node->childNodes->item($i)) instanceof DOMCharacterData) {
                    $node->removeChild($child);
                }
            }
        }
    }

    /**
     * Validate list of keys in the associative array.
     *
     * @param  array $hash
     * @param  array $validKeys
     * @return array
     * @since  Method available since Release 3.3.0
     * @author Mike Naberezny <mike@maintainable.com>
     * @author Derek DeVries <derek@maintainable.com>
     */
    public static function assertValidKeys($hash, $validKeys)
    {
        // $hash must be an array
        if ( !is_array($hash)) {
            throw new InvalidArgumentException(
              'Expected array, got ' . gettype($hash)
            );
        }

        // normalize validation keys so that we can use both key/associative arrays
        foreach ($validKeys as $key => $val) {
            is_int($key) ? $valids[$val] = NULL : $valids[$key] = $val;
        }

        // check for invalid keys
        foreach ($hash as $key => $value) {
            if (!in_array($key, array_keys($valids))) {
                $unknown[] = $key;
            }
        }

        if (!empty($unknown)) {
            throw new InvalidArgumentException(
              'Unknown key(s): '.implode(', ', $unknown)
            );
        }

        // add default values for any valid keys that are empty
        foreach ($valids as $key => $value) {
            if (!isset($hash[$key])) {
              $hash[$key] = $value;
            }
        }

        return $hash;
    }

    /**
     * Parse a CSS selector into an associative array suitable for
     * use with findNodes().
     *
     * @param  string $selector
     * @param  mixed  $content
     * @return array
     * @since  Method available since Release 3.3.0
     * @author Mike Naberezny <mike@maintainable.com>
     * @author Derek DeVries <derek@maintainable.com>
     */
    public static function convertSelectToTag($selector, $content = TRUE)
    {
        $selector = trim(preg_replace("/\s+/", " ", $selector));

        // substitute spaces within attribute value
        while (preg_match('/\[[^\]]+"[^"]+\s[^"]+"\]/', $selector)) {
            $selector = preg_replace('/(\[[^\]]+"[^"]+)\s([^"]+"\])/', "$1__SPACE__$2", $selector);
        }

        $elements    = strstr($selector, ' ') ? explode(' ', $selector) : array($selector);
        $previousTag = array();

        foreach (array_reverse($elements) as $element) {
            $element = str_replace('__SPACE__', ' ', $element);

            // child selector
            if ($element == '>') {
                $previousTag = array('child' => $previousTag['descendant']);
                continue;
            }

            $tag = array();

            // match element tag
            preg_match("/^([^\.#\[]*)/", $element, $eltMatches);

            if (!empty($eltMatches[1])) {
                $tag['tag'] = $eltMatches[1];
            }

            // match attributes (\[[^\]]*\]*), ids (#[^\.#\[]*), and classes (\.[^\.#\[]*))
            preg_match_all("/(\[[^\]]*\]*|#[^\.#\[]*|\.[^\.#\[]*)/", $element, $matches);

            if (!empty($matches[1])) {
                $classes = array();
                $attrs   = array();

                foreach ($matches[1] as $match) {
                    // id matched
                    if (substr($match, 0, 1) == '#') {
                        $tag['id'] = substr($match, 1);
                    }

                    // class matched
                    else if (substr($match, 0, 1) == '.') {
                        $classes[] = substr($match, 1);
                    }

                    // attribute matched
                    else if (substr($match, 0, 1) == '[' && substr($match, -1, 1) == ']') {
                        $attribute = substr($match, 1, strlen($match) - 2);
                        $attribute = str_replace('"', '', $attribute);

                        // match single word
                        if (strstr($attribute, '~=')) {
                            list($key, $value) = explode('~=', $attribute);
                            $value = "regexp:/.*\b$value\b.*/";
                        }

                        // match substring
                        else if (strstr($attribute, '*=')) {
                            list($key, $value) = explode('*=', $attribute);
                            $value = "regexp:/.*$value.*/";
                        }

                        // exact match
                        else {
                            list($key, $value) = explode('=', $attribute);
                        }

                        $attrs[$key] = $value;
                    }
                }

                if ($classes) {
                    $tag['class'] = join(' ', $classes);
                }

                if ($attrs) {
                    $tag['attributes'] = $attrs;
                }
            }

            // tag content
            if (is_string($content)) {
                $tag['content'] = $content;
            }

            // determine previous child/descendants
            if (!empty($previousTag['descendant'])) {
                $tag['descendant'] = $previousTag['descendant'];
            }

            else if (!empty($previousTag['child'])) {
                $tag['child'] = $previousTag['child'];
            }

            $previousTag = array('descendant' => $tag);
        }

        return $tag;
    }

    /**
     * Parse an $actual document and return an array of DOMNodes
     * matching the CSS $selector.  If an error occurs, it will
     * return FALSE.
     *
     * To only return nodes containing a certain content, give
     * the $content to match as a string.  Otherwise, setting
     * $content to TRUE will return all nodes matching $selector.
     *
     * The $actual document may be a DOMDocument or a string
     * containing XML or HTML, identified by $isHtml.
     *
     * @param  array   $selector
     * @param  string  $content
     * @param  mixed   $actual
     * @param  boolean $isHtml
     * @return false|array
     * @since  Method available since Release 3.3.0
     * @author Mike Naberezny <mike@maintainable.com>
     * @author Derek DeVries <derek@maintainable.com>
     */
    public static function cssSelect($selector, $content, $actual, $isHtml = TRUE)
    {
        $matcher = self::convertSelectToTag($selector, $content);
        $dom     = self::load($actual, $isHtml);
        $tags    = self::findNodes($dom, $matcher);

        return $tags;
    }

    /**
     * Parse out the options from the tag using DOM object tree.
     *
     * @param  DOMDocument $dom
     * @param  array       $options
     * @return array
     * @since  Method available since Release 3.3.0
     * @author Mike Naberezny <mike@maintainable.com>
     * @author Derek DeVries <derek@maintainable.com>
     */
    public static function findNodes(DOMDocument $dom, array $options)
    {
        $valid = array(
          'id', 'class', 'tag', 'content', 'attributes', 'parent',
          'child', 'ancestor', 'descendant', 'children'
        );

        $filtered = array();
        $options  = self::assertValidKeys($options, $valid);

        // find the element by id
        if ($options['id']) {
            $options['attributes']['id'] = $options['id'];
        }

        if ($options['class']) {
            $options['attributes']['class'] = $options['class'];
        }

        // find the element by a tag type
        if ($options['tag']) {
            $elements = $dom->getElementsByTagName($options['tag']);

            foreach ($elements as $element) {
                $nodes[] = $element;
            }

            if (empty($nodes)) {
                return FALSE;
            }

        // no tag selected, get them all
        } else {
            $tags = array(
              'a', 'abbr', 'acronym', 'address', 'area', 'b', 'base', 'bdo',
              'big', 'blockquote', 'body', 'br', 'button', 'caption', 'cite',
              'code', 'col', 'colgroup', 'dd', 'del', 'div', 'dfn', 'dl',
              'dt', 'em', 'fieldset', 'form', 'frame', 'frameset', 'h1', 'h2',
              'h3', 'h4', 'h5', 'h6', 'head', 'hr', 'html', 'i', 'iframe',
              'img', 'input', 'ins', 'kbd', 'label', 'legend', 'li', 'link',
              'map', 'meta', 'noframes', 'noscript', 'object', 'ol', 'optgroup',
              'option', 'p', 'param', 'pre', 'q', 'samp', 'script', 'select',
              'small', 'span', 'strong', 'style', 'sub', 'sup', 'table',
              'tbody', 'td', 'textarea', 'tfoot', 'th', 'thead', 'title',
              'tr', 'tt', 'ul', 'var'
            );

            foreach ($tags as $tag) {
                $elements = $dom->getElementsByTagName($tag);

                foreach ($elements as $element) {
                    $nodes[] = $element;
                }
            }

            if (empty($nodes)) {
                return FALSE;
            }
        }

        // filter by attributes
        if ($options['attributes']) {
            foreach ($nodes as $node) {
                $invalid = FALSE;

                foreach ($options['attributes'] as $name => $value) {
                    // match by regexp if like "regexp:/foo/i"
                    if (preg_match('/^regexp\s*:\s*(.*)/i', $value, $matches)) {
                        if (!preg_match($matches[1], $node->getAttribute($name))) {
                            $invalid = TRUE;
                        }
                    }

                    // class can match only a part
                    else if ($name == 'class') {
                        // split to individual classes
                        $findClasses = explode(' ', preg_replace("/\s+/", " ", $value));
                        $allClasses  = explode(' ', preg_replace("/\s+/", " ", $node->getAttribute($name)));

                        // make sure each class given is in the actual node
                        foreach ($findClasses as $findClass) {
                            if (!in_array($findClass, $allClasses)) {
                                $invalid = TRUE;
                            }
                        }
                    }

                    // match by exact string
                    else {
                        if ($node->getAttribute($name) != $value) {
                            $invalid = TRUE;
                        }
                    }
                }

                // if every attribute given matched
                if (!$invalid) {
                    $filtered[] = $node;
                }
            }

            $nodes    = $filtered;
            $filtered = array();

            if (empty($nodes)) {
                return FALSE;
            }
        }

        // filter by content
        if ($options['content'] !== NULL) {
            foreach ($nodes as $node) {
                $invalid = FALSE;

                // match by regexp if like "regexp:/foo/i"
                if (preg_match('/^regexp\s*:\s*(.*)/i', $options['content'], $matches)) {
                    if (!preg_match($matches[1], self::getNodeText($node))) {
                        $invalid = TRUE;
                    }
                }

                // match by exact string
                else if (strstr(self::getNodeText($node), $options['content']) === FALSE) {
                    $invalid = TRUE;
                }

                if (!$invalid) {
                    $filtered[] = $node;
                }
            }

            $nodes    = $filtered;
            $filtered = array();

            if (empty($nodes)) {
                return FALSE;
            }
        }

        // filter by parent node
        if ($options['parent']) {
            $parentNodes = self::findNodes($dom, $options['parent']);
            $parentNode  = isset($parentNodes[0]) ? $parentNodes[0] : NULL;

            foreach ($nodes as $node) {
                if ($parentNode !== $node->parentNode) {
                    break;
                }

                $filtered[] = $node;
            }

            $nodes    = $filtered;
            $filtered = array();

            if (empty($nodes)) {
                return FALSE;
            }
        }

        // filter by child node
        if ($options['child']) {
            $childNodes = self::findNodes($dom, $options['child']);
            $childNodes = !empty($childNodes) ? $childNodes : array();

            foreach ($nodes as $node) {
                foreach ($node->childNodes as $child) {
                    foreach ($childNodes as $childNode) {
                        if ($childNode === $child) {
                            $filtered[] = $node;
                        }
                    }
                }
            }

            $nodes    = $filtered;
            $filtered = array();

            if (empty($nodes)) {
                return FALSE;
            }
        }

        // filter by ancestor
        if ($options['ancestor']) {
            $ancestorNodes = self::findNodes($dom, $options['ancestor']);
            $ancestorNode  = isset($ancestorNodes[0]) ? $ancestorNodes[0] : NULL;

            foreach ($nodes as $node) {
                $parent = $node->parentNode;

                while ($parent->nodeType != XML_HTML_DOCUMENT_NODE) {
                    if ($parent === $ancestorNode) {
                        $filtered[] = $node;
                    }

                    $parent = $parent->parentNode;
                }
            }

            $nodes    = $filtered;
            $filtered = array();

            if (empty($nodes)) {
                return FALSE;
            }
        }

        // filter by descendant
        if ($options['descendant']) {
            $descendantNodes = self::findNodes($dom, $options['descendant']);
            $descendantNodes = !empty($descendantNodes) ? $descendantNodes : array();

            foreach ($nodes as $node) {
                foreach (self::getDescendants($node) as $descendant) {
                    foreach ($descendantNodes as $descendantNode) {
                        if ($descendantNode === $descendant) {
                            $filtered[] = $node;
                        }
                    }
                }
            }

            $nodes    = $filtered;
            $filtered = array();

            if (empty($nodes)) {
                return FALSE;
            }
        }

        // filter by children
        if ($options['children']) {
            $validChild   = array('count', 'greater_than', 'less_than', 'only');
            $childOptions = self::assertValidKeys($options['children'], $validChild);

            foreach ($nodes as $node) {
                $childNodes = $node->childNodes;

                foreach ($childNodes as $childNode) {
                    if ($childNode->nodeType !== XML_CDATA_SECTION_NODE &&
                        $childNode->nodeType !== XML_TEXT_NODE) {
                        $children[] = $childNode;
                    }
                }

                // we must have children to pass this filter
                if (!empty($children)) {
                   // exact count of children
                    if ($childOptions['count'] !== NULL) {
                        if (count($children) !== $childOptions['count']) {
                            break;
                        }
                    }

                    // range count of children
                    else if ($childOptions['less_than']    !== NULL &&
                            $childOptions['greater_than'] !== NULL) {
                        if (count($children) >= $childOptions['less_than'] ||
                            count($children) <= $childOptions['greater_than']) {
                            break;
                        }
                    }

                    // less than a given count
                    else if ($childOptions['less_than'] !== NULL) {
                        if (count($children) >= $childOptions['less_than']) {
                            break;
                        }
                    }

                    // more than a given count
                    else if ($childOptions['greater_than'] !== NULL) {
                        if (count($children) <= $childOptions['greater_than']) {
                            break;
                        }
                    }

                    // match each child against a specific tag
                    if ($childOptions['only']) {
                        $onlyNodes = self::findNodes($dom, $childOptions['only']);

                        // try to match each child to one of the 'only' nodes
                        foreach ($children as $child) {
                            $matched = FALSE;

                            foreach ($onlyNodes as $onlyNode) {
                                if ($onlyNode === $child) {
                                    $matched = TRUE;
                                }
                            }

                            if (!$matched) {
                                break(2);
                            }
                        }
                    }

                    $filtered[] = $node;
                }
            }

            $nodes    = $filtered;
            $filtered = array();

            if (empty($nodes)) {
                return;
            }
        }

        // return the first node that matches all criteria
        return !empty($nodes) ? $nodes : array();
    }

    /**
     * Recursively get flat array of all descendants of this node.
     *
     * @param  DOMNode $node
     * @return array
     * @since  Method available since Release 3.3.0
     * @author Mike Naberezny <mike@maintainable.com>
     * @author Derek DeVries <derek@maintainable.com>
     */
    protected static function getDescendants(DOMNode $node)
    {
        $allChildren = array();
        $childNodes  = $node->childNodes ? $node->childNodes : array();

        foreach ($childNodes as $child) {
            if ($child->nodeType === XML_CDATA_SECTION_NODE ||
                $child->nodeType === XML_TEXT_NODE) {
                continue;
            }

            $children    = self::getDescendants($child);
            $allChildren = array_merge($allChildren, $children, array($child));
        }

        return isset($allChildren) ? $allChildren : array();
    }

    /**
     * Get the text value of this node's child text node.
     *
     * @param  DOMNode $node
     * @return string
     * @since  Method available since Release 3.3.0
     * @author Mike Naberezny <mike@maintainable.com>
     * @author Derek DeVries <derek@maintainable.com>
     */
    protected static function getNodeText(DOMNode $node)
    {
        $childNodes = $node->childNodes instanceof DOMNodeList ? $node->childNodes : array();
        $text       = '';

        foreach ($childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $text .= trim($child->data).' ';
            } else {
                $text .= self::getNodeText($child);
            }
        }

        return str_replace('  ', ' ', $text);
    }
}
?>
