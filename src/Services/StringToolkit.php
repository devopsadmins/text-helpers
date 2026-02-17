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
}
