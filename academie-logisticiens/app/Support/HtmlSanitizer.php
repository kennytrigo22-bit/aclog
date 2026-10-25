<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Assainit le HTML riche (contenu de cours, plans, fiches) : liste blanche de balises, attributs et styles.
 * Supprime scripts, gestionnaires d'evenements (onclick...), iframes, formulaires et URL "javascript:".
 */
class HtmlSanitizer
{
    private const TAGS = [
        'p', 'br', 'hr', 'strong', 'b', 'em', 'i', 'u', 'sup', 'sub', 'ul', 'ol', 'li',
        'h2', 'h3', 'h4', 'h5', 'table', 'thead', 'tbody', 'tr', 'th', 'td',
        'a', 'img', 'figure', 'figcaption', 'div', 'span', 'blockquote', 'code', 'pre',
    ];

    private const REMOVE_WITH_CONTENT = [
        'script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select',
        'svg', 'math', 'link', 'meta', 'base', 'frame', 'frameset', 'audio', 'video', 'source', 'noscript',
    ];

    private const ATTRS = ['class', 'style', 'href', 'target', 'rel', 'src', 'alt', 'title', 'loading', 'decoding', 'colspan', 'rowspan', 'width', 'height'];

    private const STYLE_PROPS = [
        'background', 'background-color', 'color', 'text-align', 'font-weight', 'font-style', 'text-decoration',
        'border', 'border-left', 'border-top', 'border-bottom', 'border-right', 'border-color',
        'padding', 'width', 'max-width', 'height', 'vertical-align',
    ];

    public static function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="UTF-8"><div id="__root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NONET);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $dom->getElementById('__root');
        if (!$root) {
            return e($html);
        }

        self::walk($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }

        return $out;
    }

    private static function walk(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if (!$child instanceof DOMElement) {
                if ($child->nodeType === XML_COMMENT_NODE || $child->nodeType === XML_PI_NODE) {
                    $node->removeChild($child);
                }
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::REMOVE_WITH_CONTENT, true)) {
                $node->removeChild($child);
                continue;
            }

            self::walk($child);

            if (!in_array($tag, self::TAGS, true)) {
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }

            self::cleanAttributes($child, $tag);
        }
    }

    private static function cleanAttributes(DOMElement $el, string $tag): void
    {
        foreach (iterator_to_array($el->attributes) as $attr) {
            $name = strtolower($attr->name);
            $value = $attr->value;

            if (!in_array($name, self::ATTRS, true) || str_starts_with($name, 'on')) {
                $el->removeAttribute($attr->name);
                continue;
            }

            if ($name === 'href' && !self::urlOk($value, ['http', 'https', 'mailto', 'tel'], true)) {
                $el->removeAttribute($attr->name);
            } elseif ($name === 'src' && ($tag !== 'img' || !self::urlOk($value, ['http', 'https'], true, true))) {
                $el->removeAttribute($attr->name);
            } elseif ($name === 'style') {
                $clean = self::cleanStyle($value);
                $clean === '' ? $el->removeAttribute('style') : $el->setAttribute('style', $clean);
            } elseif ($name === 'class') {
                $el->setAttribute('class', preg_replace('/[^A-Za-z0-9_\- ]/', '', $value));
            }
        }

        if ($tag === 'a' && $el->hasAttribute('href')) {
            $el->setAttribute('target', '_blank');
            $el->setAttribute('rel', 'noopener noreferrer');
        }
    }

    private static function urlOk(string $url, array $schemes, bool $allowRelative, bool $allowDataImage = false): bool
    {
        $url = trim(preg_replace('/[\x00-\x20]+/', '', html_entity_decode($url)));
        if ($url === '') {
            return false;
        }
        if ($allowRelative && (str_starts_with($url, '/') && !str_starts_with($url, '//') || str_starts_with($url, '#'))) {
            return true;
        }
        if ($allowDataImage && preg_match('#^data:image/(png|jpe?g|gif|webp);base64,[A-Za-z0-9+/=]+$#i', $url)) {
            return true;
        }
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        return $scheme !== '' && in_array($scheme, $schemes, true);
    }

    private static function cleanStyle(string $style): string
    {
        $out = [];
        foreach (explode(';', $style) as $declaration) {
            if (!str_contains($declaration, ':')) {
                continue;
            }
            [$prop, $val] = array_map('trim', explode(':', $declaration, 2));
            $prop = strtolower($prop);
            $lower = strtolower($val);
            if (!in_array($prop, self::STYLE_PROPS, true)) {
                continue;
            }
            if (preg_match('/url\s*\(|expression|javascript:|@import|behavior|\\\\/', $lower)) {
                continue;
            }
            if (!preg_match('/^[#a-z0-9%.,\s()\-+]+$/i', $val)) {
                continue;
            }
            $out[] = "{$prop}:{$val}";
        }

        return implode(';', $out);
    }
}
