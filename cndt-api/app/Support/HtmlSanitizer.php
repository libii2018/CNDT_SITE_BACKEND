<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Nettoyage du HTML riche provenant du dashboard (éditeur Tiptap,
 * import .docx, collage…).
 *
 * Le contenu est stocké côté API puis réinjecté dans des pages du
 * même domaine que le dashboard : tout <script>, gestionnaire on*
 * ou URL javascript:/ inséré dans un article doit être neutralisé
 * AVANT stockage, sinon un article piégé permet de voler la session
 * d'un administrateur (défense en profondeur avec le token en
 * sessionStorage et le jeton Sanctum à expiration).
 */
class HtmlSanitizer
{
    /** Balises supprimées avec leur contenu. */
    private const DROP_TAGS = ['script', 'style', 'iframe', 'frame', 'object', 'embed', 'noscript'];

    /** Balises conservées (tout le reste est retiré, contenu préservé). */
    private const KEEP_TAGS = [
        'p', 'br', 'hr', 'div', 'span', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'li', 'blockquote', 'pre', 'code', 'strong', 'em', 'b',
        'i', 'u', 's', 'sub', 'sup', 'a', 'img', 'figure', 'figcaption',
        'table', 'thead', 'tbody', 'tr', 'th', 'td', 'caption', 'colgroup',
    ];

    /** Attributs globaux autorisés (tout attribut on* est rejeté). */
    private const KEEP_ATTRS = ['href', 'src', 'alt', 'title', 'target', 'rel', 'colspan', 'rowspan'];

    /** Protocoles autorisés dans href/src (relatifs acceptés). */
    private const SAFE_PROTOCOLS = ['http', 'https', 'mailto'];

    public static function clean(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $html = trim((string) $html);
        if ($html === '') {
            return $html;
        }

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="utf-8"?><body>'.$html.'</body>',
            LIBXML_NONET | LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        $body = $dom->getElementsByTagName('body')->item(0);
        if ($body === null) {
            return '';
        }

        self::walk($body);

        $out = '';
        foreach ($body->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }

        return trim($out);
    }

    private static function walk(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes ?? []) as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::DROP_TAGS, true)) {
                $child->parentNode?->removeChild($child);

                continue;
            }

            if (! in_array($tag, self::KEEP_TAGS, true)) {
                // Balise non autorisée : retirer la balise, garder le texte
                self::walk($child);
                while ($child->firstChild !== null) {
                    $child->parentNode?->insertBefore($child->firstChild, $child);
                }
                $child->parentNode?->removeChild($child);

                continue;
            }

            self::sanitizeAttributes($child);
            self::walk($child);
        }
    }

    private static function sanitizeAttributes(DOMElement $el): void
    {
        foreach (iterator_to_array($el->attributes) as $attr) {
            $name = strtolower($attr->name);
            $value = trim($attr->value);

            // Tout gestionnaire d'événement (onclick, onerror…) disparaît
            if (str_starts_with($name, 'on')) {
                $el->removeAttribute($attr->name);

                continue;
            }

            if (! in_array($name, self::KEEP_ATTRS, true)) {
                $el->removeAttribute($attr->name);

                continue;
            }

            if (in_array($name, ['href', 'src'], true)) {
                if (! self::isSafeUrl($value)) {
                    $el->removeAttribute($attr->name);
                }
            }
        }

        // Les liens externes s'ouvrent sans accès au contexte courant
        if (strtolower($el->tagName) === 'a' && $el->getAttribute('target') === '_blank') {
            $el->setAttribute('rel', trim($el->getAttribute('rel').' noopener noreferrer'));
        }
    }

    private static function isSafeUrl(string $url): bool
    {
        if ($url === '' || str_starts_with($url, '#') || str_starts_with($url, '/')) {
            return true;
        }

        if (preg_match('/^([a-z][a-z0-9+.-]*):/i', $url, $m)) {
            return in_array(strtolower($m[1]), self::SAFE_PROTOCOLS, true);
        }

        return true; // relatif
    }
}
