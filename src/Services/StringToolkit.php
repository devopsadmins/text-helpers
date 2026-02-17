<?php

declare(strict_types=1);

namespace Arbe\TextHelpers\Services;

/**
 * Serviço de manipulação de texto de alta performance.
 * Utiliza Readonly Class do PHP 8.3 para segurança e integridade.
 */
readonly class StringToolkit
{
    /**
     * Separa o nome completo em primeiro nome e sobrenome.
     * @param string $fullName
     * @return array{firstName: string, lastName: string}
     */
    public function splitName(string $fullName): array
    {
        $parts = explode(' ', trim($fullName));
        $firstName = array_shift($parts) ?? '';
        $lastName = implode(' ', $parts);

        return [
            'firstName' => $firstName,
            'lastName'  => $lastName ?: ''
        ];
    }

    /**
     * Abrevia nomes intermediários mantendo o primeiro e o último.
     * Ex: "Vinícius Dias de Souza" -> "Vinícius D. Souza"
     */
    public function abbreviate(string $name, int $maxLength = 20): string
    {
        if (mb_strlen($name) <= $maxLength) {
            return $name;
        }

        $parts = explode(' ', trim($name));
        if (count($parts) <= 2) {
            return $name;
        }

        $first = array_shift($parts);
        $last = array_pop($parts);

        $middle = array_map(function ($part) {
            // Ignora conectores comuns em PT-BR
            if (in_array(mb_strtolower($part), ['de', 'da', 'do', 'dos', 'das', 'e'])) {
                return null;
            }
            return mb_substr($part, 0, 1) . '.';
        }, $parts);

        $middle = array_filter($middle);
        
        $result = trim($first . ' ' . implode(' ', $middle) . ' ' . $last);

        return $result;
    }

    /**
     * Formata nomes próprios para Title Case, respeitando preposições em minúsculo (pt-BR).
     * Ex: "VINICIUS DE SOUZA" -> "Vinícius de Souza"
     */
    public function formatName(string $name): string
    {
        $name = mb_convert_case($name, MB_CASE_TITLE, 'UTF-8');

        $prepositions = [' de ', ' da ', ' do ', ' dos ', ' das ', ' e '];

        foreach ($prepositions as $prep) {
            $name = str_ireplace($prep, mb_strtolower($prep), $name);
        }

        return $name;
    }

    /**
     * Mascara dados sensíveis.
     * Se for email, mascara parte do local e do domínio.
     * Ex: "eolimabr@gmail.com" -> "eol*****@g****.com"
     * Ex: "12345678900" -> "123********"
     */
    public function mask(string $value, int $visibleChars = 3, string $maskChar = '*'): string
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $parts = explode('@', $value);
            $local = $parts[0];
            $domain = $parts[1] ?? '';

            // Mascara parte local
            if (mb_strlen($local) > $visibleChars) {
                $localVisible = mb_substr($local, 0, $visibleChars);
                $localMasked = str_repeat($maskChar, mb_strlen($local) - $visibleChars);
                $local = $localVisible . $localMasked;
            }

            // Mascara domínio
            $domainParts = explode('.', $domain);
            $domainName = $domainParts[0];
            $tld = implode('.', array_slice($domainParts, 1));

            // Mantém apenas a primeiro letra do domínio visível se tiver pelo menos 2 chars
            if (mb_strlen($domainName) > 1) {
                 $domainVisible = mb_substr($domainName, 0, 1);
                 $domainMasked = str_repeat($maskChar, mb_strlen($domainName) - 1);
                 $domainName = $domainVisible . $domainMasked;
            }

            return "{$local}@{$domainName}.{$tld}";
        }

        if (mb_strlen($value) <= $visibleChars) {
            return $value;
        }

        $visiblePart = mb_substr($value, 0, $visibleChars);
        $maskedPart = str_repeat($maskChar, mb_strlen($value) - $visibleChars);

        return $visiblePart . $maskedPart;
    }

    /**
     * Extrai as iniciais do nome.
     * Padrão: Primeira letra do primeiro e do último nome.
     * Ex: "Vinícius Dias de Souza" -> "VS"
     */
    public function initials(string $name, int $length = 2): string
    {
        $name = trim($name);
        if (empty($name)) {
            return '';
        }

        $parts = explode(' ', $name);
        // Filtra partes vazias
        $parts = array_filter($parts, fn($p) => mb_strlen($p) > 0);
        $parts = array_values($parts); // Reindex

        if (count($parts) === 0) {
            return '';
        }

        if (count($parts) === 1) {
            return mb_strtoupper(mb_substr($parts[0], 0, $length));
        }

        $first = mb_substr($parts[0], 0, 1);
        $last = mb_substr(end($parts), 0, 1);

        return mb_strtoupper($first . $last);
    }

    /**
     * Remove espaços duplos, tabs, quebras de linha e caracteres não-imprimíveis.
     * Ex: "  Nome    Sobrenome  " -> "Nome Sobrenome"
     */
    public function clean(string $text): string
    {
        // Remove caracteres de controle invisíveis (exceto os básicos se desejado, mas 'clean' sugere remover tudo)
        // \p{C} matches invisible control characters
        $text = preg_replace('/[\x00-\x1F\x7F]/u', '', $text);

        // Substitui múltiplos espaços por um único
        $text = preg_replace('/\s+/u', ' ', $text);

        return trim($text);
    }

    /**
     * Estima o tempo de leitura em minutos.
     */
    public function readTime(string $content, int $wpm = 200): int
    {
        // Conta palavras de forma compatível com UTF-8 básico
        // str_word_count nem sempre funciona bem com acentos, então usamos regex simples
        $wordCount = count(preg_split('/\s+/u', strip_tags($content), -1, PREG_SPLIT_NO_EMPTY));
        return (int) ceil($wordCount / $wpm);
    }

    /**
     * Verifica terminologias bloqueadas (Profanity Filter básico).
     */
    public function isClean(string $text, array $customDictionary = []): bool
    {
        // Lista placeholder
        $blocklist = array_merge(['palavrao', 'feio'], $customDictionary);

        $lowerText = mb_strtolower($text);

        foreach ($blocklist as $term) {
            if (empty($term)) continue;
            if (mb_strpos($lowerText, mb_strtolower($term)) !== false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Gera um ID curto legível (sem caracteres ambíguos 0, O, 1, I, l).
     */
    public function shortId(int $length = 6): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $max = strlen($alphabet) - 1;
        $id = '';

        for ($i = 0; $i < $length; $i++) {
            $id .= $alphabet[random_int(0, $max)];
        }

        return $id;
    }

    /**
     * Cria slug customizado mantendo caracteres especiais especificados.
     * Ex: slugWithSpecialChars("@devops_admins #laravel", ['@', '#']) -> "@devops-admins-#laravel"
     * 
     * @param string $text
     * @param array<string> $keepChars Caracteres a manter no slug
     * @param string $separator Separador (padrão: '-')
     * @return string
     */
    public function slugWithSpecialChars(string $text, array $keepChars = [], string $separator = '-'): string
    {
        // Primeiro, substitui os caracteres especiais que queremos manter por placeholders
        $placeholders = [];
        foreach ($keepChars as $index => $char) {
            $placeholder = "___KEEP_{$index}___";
            $placeholders[$placeholder] = $char;
            $text = str_replace($char, $placeholder, $text);
        }

        // Transliteração básica para caracteres latinos
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        
        // Remove tudo que não for alfanumérico, espaço, hífen ou placeholder
        $text = preg_replace('/[^a-zA-Z0-9\s\-_]+/', '', $text);
        
        // Substitui espaços e underscores por separador
        $text = preg_replace('/[\s_]+/', $separator, $text);
        
        // Remove múltiplos separadores consecutivos
        $text = preg_replace("/{$separator}+/", $separator, $text);
        
        // Restaura os caracteres especiais
        foreach ($placeholders as $placeholder => $char) {
            $text = str_replace($placeholder, $char, $text);
        }
        
        // Remove separadores no início e fim
        $text = trim($text, $separator);
        
        return mb_strtolower($text);
    }

    /**
     * Corta HTML de forma inteligente sem quebrar tags.
     * Mantém a estrutura HTML fechando tags corretamente.
     * 
     * @param string $html
     * @param int $maxLength Limite de caracteres de texto visível
     * @param string $ellipsis Texto a adicionar no final (padrão: '...')
     * @return string
     */
    public function truncateHtml(string $html, int $maxLength = 100, string $ellipsis = '...'): string
    {
        if (mb_strlen(strip_tags($html)) <= $maxLength) {
            return $html;
        }

        $dom = new \DOMDocument();
        // Suprime warnings de HTML mal formado
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        
        $length = 0;
        $truncated = false;
        
        $this->truncateNode($dom->documentElement, $maxLength, $length, $truncated);
        
        // Remove o wrapper XML
        $result = $dom->saveHTML();
        $result = preg_replace('/^<\?xml[^>]+>/', '', $result);
        
        if ($truncated) {
            // Adiciona ellipsis antes da última tag de fechamento se houver
            $result = preg_replace('/(<\/\w+>)?$/', $ellipsis . '$1', $result, 1);
        }
        
        return trim($result);
    }

    /**
     * Função auxiliar recursiva para truncar nós do DOM.
     */
    private function truncateNode(\DOMNode $node, int $maxLength, int &$length, bool &$truncated): void
    {
        if ($truncated) {
            return;
        }

        if ($node->nodeType === XML_TEXT_NODE) {
            $textLength = mb_strlen($node->nodeValue);
            
            if ($length + $textLength > $maxLength) {
                $remaining = $maxLength - $length;
                $node->nodeValue = mb_substr($node->nodeValue, 0, $remaining);
                $truncated = true;
                $length = $maxLength;
            } else {
                $length += $textLength;
            }
        } elseif ($node->hasChildNodes()) {
            $children = [];
            foreach ($node->childNodes as $child) {
                $children[] = $child;
            }
            
            foreach ($children as $child) {
                $this->truncateNode($child, $maxLength, $length, $truncated);
                
                if ($truncated) {
                    // Remove nós seguintes
                    while ($child->nextSibling) {
                        $node->removeChild($child->nextSibling);
                    }
                    break;
                }
            }
        }
    }

    /**
     * Destaca termos de busca em um texto usando tags HTML (padrão: <mark>).
     * Mantém case original do texto.
     * 
     * @param string $text
     * @param string|array<string> $keywords Palavra(s) a destacar
     * @param string $tag Tag HTML a usar (padrão: 'mark')
     * @return string
     */
    public function highlight(string $text, string|array $keywords, string $tag = 'mark'): string
    {
        if (empty($keywords)) {
            return $text;
        }

        $keywords = is_array($keywords) ? $keywords : [$keywords];
        
        foreach ($keywords as $keyword) {
            if (empty($keyword)) {
                continue;
            }
            
            // Escape special regex characters
            $pattern = preg_quote($keyword, '/');
            
            // Case insensitive replacement mantendo case original
            $text = preg_replace(
                "/($pattern)/iu",
                "<{$tag}>$1</{$tag}>",
                $text
            );
        }

        return $text;
    }

    /**
     * Remove emojis de uma string.
     * Útil para bancos de dados que não suportam utf8mb4.
     * 
     * @param string $text
     * @return string
     */
    public function stripEmojis(string $text): string
    {
        // Remove emojis e outros símbolos Unicode
        return preg_replace('/[\x{1F600}-\x{1F64F}]|[\x{1F300}-\x{1F5FF}]|[\x{1F680}-\x{1F6FF}]|[\x{1F1E0}-\x{1F1FF}]|[\x{2600}-\x{26FF}]|[\x{2700}-\x{27BF}]|[\x{1F900}-\x{1F9FF}]|[\x{1FA70}-\x{1FAFF}]/u', '', $text);
    }

    /**
     * Converte shortcodes em emojis.
     * Ex: :smile: -> 😀, :heart: -> ❤️
     * 
     * @param string $text
     * @return string
     */
    public function emojify(string $text): string
    {
        $emojiMap = [
            ':smile:' => '😀',
            ':grin:' => '😁',
            ':joy:' => '😂',
            ':heart:' => '❤️',
            ':fire:' => '🔥',
            ':thumbsup:' => '👍',
            ':thumbsdown:' => '👎',
            ':check:' => '✅',
            ':cross:' => '❌',
            ':star:' => '⭐',
            ':rocket:' => '🚀',
            ':warning:' => '⚠️',
            ':info:' => 'ℹ️',
            ':cool:' => '😎',
            ':thinking:' => '🤔',
            ':party:' => '🎉',
            ':wave:' => '👋',
        ];

        foreach ($emojiMap as $shortcode => $emoji) {
            $text = str_replace($shortcode, $emoji, $text);
        }

        return $text;
    }

    /**
     * Converte valor monetário para extenso em português (Brasil).
     * Ex: 150.50 -> "cento e cinquenta reais e cinquenta centavos"
     * 
     * @param float|int $value
     * @return string
     */
    public function moneyToWords(float|int $value): string
    {
        $value = round($value, 2);
        $reais = (int) floor($value);
        $centavos = (int) round(($value - $reais) * 100);

        $result = [];

        // Parte dos reais
        if ($reais === 0) {
            $result[] = 'zero';
        } else {
            $result[] = $this->numberToWords($reais);
        }

        // Unidade monetária (real/reais)
        if ($reais === 1) {
            $result[] = 'real';
        } else {
            $result[] = 'reais';
        }

        // Parte dos centavos
        if ($centavos > 0) {
            $result[] = 'e';
            $result[] = $this->numberToWords($centavos);
            $result[] = $centavos === 1 ? 'centavo' : 'centavos';
        }

        return implode(' ', $result);
    }

    /**
     * Converte número para extenso em português.
     * Suporta números até 999.999.999.
     */
    private function numberToWords(int $number): string
    {
        $units = ['', 'um', 'dois', 'três', 'quatro', 'cinco', 'seis', 'sete', 'oito', 'nove'];
        $teens = ['dez', 'onze', 'doze', 'treze', 'quatorze', 'quinze', 'dezesseis', 'dezessete', 'dezoito', 'dezenove'];
        $tens = ['', '', 'vinte', 'trinta', 'quarenta', 'cinquenta', 'sessenta', 'setenta', 'oitenta', 'noventa'];
        $hundreds = ['', 'cento', 'duzentos', 'trezentos', 'quatrocentos', 'quinhentos', 'seiscentos', 'setecentos', 'oitocentos', 'novecentos'];

        if ($number === 0) {
            return 'zero';
        }

        if ($number === 100) {
            return 'cem';
        }

        $words = [];

        // Milhões
        if ($number >= 1000000) {
            $millions = (int) floor($number / 1000000);
            if ($millions === 1) {
                $words[] = 'um milhão';
            } else {
                $words[] = $this->numberToWords($millions) . ' milhões';
            }
            $number %= 1000000;
            if ($number > 0) {
                $words[] = $number < 100 ? 'e' : '';
            }
        }

        // Milhares
        if ($number >= 1000) {
            $thousands = (int) floor($number / 1000);
            if ($thousands === 1) {
                $words[] = 'mil';
            } else {
                $words[] = $this->numberToWords($thousands) . ' mil';
            }
            $number %= 1000;
            if ($number > 0 && $number < 100) {
                $words[] = 'e';
            }
        }

        // Centenas
        if ($number >= 100) {
            $hundred = (int) floor($number / 100);
            $words[] = $hundreds[$hundred];
            $number %= 100;
            if ($number > 0) {
                $words[] = 'e';
            }
        }

        // Dezenas e unidades
        if ($number >= 20) {
            $ten = (int) floor($number / 10);
            $words[] = $tens[$ten];
            $number %= 10;
            if ($number > 0) {
                $words[] = 'e';
            }
        } elseif ($number >= 10) {
            $words[] = $teens[$number - 10];
            $number = 0;
        }

        // Unidades
        if ($number > 0 && $number < 10) {
            $words[] = $units[$number];
        }

        return trim(implode(' ', array_filter($words)));
    }

    /**
     * Extrai menções (@usuario) de um texto.
     * 
     * @param string $text
     * @return array<string> Array único de menções (sem duplicatas)
     */
    public function extractMentions(string $text): array
    {
        preg_match_all('/@([a-zA-Z0-9_]+)/', $text, $matches);
        return array_unique($matches[1]);
    }

    /**
     * Extrai hashtags (#tag) de um texto.
     * 
     * @param string $text
     * @return array<string> Array único de hashtags (sem duplicatas)
     */
    public function extractHashtags(string $text): array
    {
        preg_match_all('/#([a-zA-Z0-9_]+)/', $text, $matches);
        return array_unique($matches[1]);
    }

    /**
     * Converte Markdown para texto simples.
     * Remove formatação de links, negrito, itálico, imagens, etc.
     * 
     * @param string $markdown
     * @return string
     */
    public function markdownToPlainText(string $markdown): string
    {
        // Remove imagens ![alt](url)
        $text = preg_replace('/!\[([^\]]*)\]\([^\)]+\)/', '$1', $markdown);
        
        // Remove links [text](url) e mantém apenas o texto
        $text = preg_replace('/\[([^\]]+)\]\([^\)]+\)/', '$1', $text);
        
        // Remove referências de links [text][ref]
        $text = preg_replace('/\[([^\]]+)\]\[[^\]]+\]/', '$1', $text);
        
        // Remove headers (###)
        $text = preg_replace('/^#{1,6}\s+/m', '', $text);
        
        // Remove negrito e itálico (**text**, *text*, __text__, _text_)
        $text = preg_replace('/(\*\*|__)(.*?)\1/', '$2', $text);
        $text = preg_replace('/(\*|_)(.*?)\1/', '$2', $text);
        
        // Remove código inline (`code`)
        $text = preg_replace('/`([^`]+)`/', '$1', $text);
        
        // Remove blocos de código (```code```)
        $text = preg_replace('/```[\s\S]*?```/', '', $text);
        
        // Remove blockquotes (>)
        $text = preg_replace('/^>\s+/m', '', $text);
        
        // Remove listas (-, *, +, 1.)
        $text = preg_replace('/^[\*\-\+]\s+/m', '', $text);
        $text = preg_replace('/^\d+\.\s+/m', '', $text);
        
        // Remove horizontal rules (---, ***, ___)
        $text = preg_replace('/^[\-\*_]{3,}$/m', '', $text);
        
        // Limpa múltiplas linhas vazias
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        
        return trim($text);
    }
}
