<?php

declare(strict_types=1);

namespace Arbe\TextHelpers\Services;

/**
 * Interface fluida para manipulação de strings encadeadas.
 * Permite method chaining para operações sequenciais.
 * 
 * Exemplo: 
 * text("  VINICIUS DE SOUZA  ")->clean()->formatName()->abbreviate(10);
 */
class FluentString
{
    private string $value;
    private StringToolkit $toolkit;

    public function __construct(string $value, ?StringToolkit $toolkit = null)
    {
        $this->value = $value;
        $this->toolkit = $toolkit ?? new StringToolkit();
    }

    /**
     * Retorna o valor final da string.
     */
    public function toString(): string
    {
        return $this->value;
    }

    /**
     * Magic method para permitir echo/string casting.
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Retorna o valor (alias para toString).
     */
    public function get(): string
    {
        return $this->value;
    }

    // ========================================
    // Métodos Fluidos (retornam $this)
    // ========================================

    /**
     * Remove espaços duplos e caracteres não-imprimíveis.
     */
    public function clean(): self
    {
        $this->value = $this->toolkit->clean($this->value);
        return $this;
    }

    /**
     * Formata nome próprio (Title Case com preposições em minúsculo).
     */
    public function formatName(): self
    {
        $this->value = $this->toolkit->formatName($this->value);
        return $this;
    }

    /**
     * Abrevia nomes intermediários.
     */
    public function abbreviate(int $maxLength = 20): self
    {
        $this->value = $this->toolkit->abbreviate($this->value, $maxLength);
        return $this;
    }

    /**
     * Mascara dados sensíveis.
     */
    public function mask(int $visibleChars = 3, string $maskChar = '*'): self
    {
        $this->value = $this->toolkit->mask($this->value, $visibleChars, $maskChar);
        return $this;
    }

    /**
     * Cria slug customizado com caracteres especiais.
     */
    public function slugWithSpecialChars(array $keepChars = [], string $separator = '-'): self
    {
        $this->value = $this->toolkit->slugWithSpecialChars($this->value, $keepChars, $separator);
        return $this;
    }

    /**
     * Cria slug padrão (atalho para slugWithSpecialChars).
     */
    public function slug(string $separator = '-'): self
    {
        $this->value = $this->toolkit->slugWithSpecialChars($this->value, [], $separator);
        return $this;
    }

    /**
     * Trunca HTML de forma inteligente.
     */
    public function truncateHtml(int $maxLength = 100, string $ellipsis = '...'): self
    {
        $this->value = $this->toolkit->truncateHtml($this->value, $maxLength, $ellipsis);
        return $this;
    }

    /**
     * Destaca palavras-chave em HTML.
     */
    public function highlight(string|array $keywords, string $tag = 'mark'): self
    {
        $this->value = $this->toolkit->highlight($this->value, $keywords, $tag);
        return $this;
    }

    /**
     * Remove emojis da string.
     */
    public function stripEmojis(): self
    {
        $this->value = $this->toolkit->stripEmojis($this->value);
        return $this;
    }

    /**
     * Converte shortcodes em emojis.
     */
    public function emojify(): self
    {
        $this->value = $this->toolkit->emojify($this->value);
        return $this;
    }

    /**
     * Converte Markdown para texto simples.
     */
    public function markdownToPlainText(): self
    {
        $this->value = $this->toolkit->markdownToPlainText($this->value);
        return $this;
    }

    /**
     * Converte para maiúsculas.
     */
    public function upper(): self
    {
        $this->value = mb_strtoupper($this->value);
        return $this;
    }

    /**
     * Converte para minúsculas.
     */
    public function lower(): self
    {
        $this->value = mb_strtolower($this->value);
        return $this;
    }

    /**
     * Converte para Title Case.
     */
    public function title(): self
    {
        $this->value = mb_convert_case($this->value, MB_CASE_TITLE, 'UTF-8');
        return $this;
    }

    /**
     * Aplica trim na string.
     */
    public function trim(string $characters = " \t\n\r\0\x0B"): self
    {
        $this->value = trim($this->value, $characters);
        return $this;
    }

    /**
     * Substitui substring.
     */
    public function replace(string $search, string $replace): self
    {
        $this->value = str_replace($search, $replace, $this->value);
        return $this;
    }

    /**
     * Limita o tamanho da string.
     */
    public function limit(int $limit, string $end = '...'): self
    {
        if (mb_strlen($this->value) <= $limit) {
            return $this;
        }
        
        $this->value = mb_substr($this->value, 0, $limit) . $end;
        return $this;
    }

    // ========================================
    // Métodos que retornam valores (terminam a cadeia)
    // ========================================

    /**
     * Separa o nome em primeiro nome e sobrenome.
     * @return array{firstName: string, lastName: string}
     */
    public function splitName(): array
    {
        return $this->toolkit->splitName($this->value);
    }

    /**
     * Extrai as iniciais.
     */
    public function initials(int $length = 2): string
    {
        return $this->toolkit->initials($this->value, $length);
    }

    /**
     * Calcula tempo de leitura.
     */
    public function readTime(int $wpm = 200): int
    {
        return $this->toolkit->readTime($this->value, $wpm);
    }

    /**
     * Verifica se o texto é limpo (sem profanidade).
     */
    public function isClean(array $customDictionary = []): bool
    {
        return $this->toolkit->isClean($this->value, $customDictionary);
    }

    /**
     * Extrai menções (@usuario).
     * @return array<string>
     */
    public function extractMentions(): array
    {
        return $this->toolkit->extractMentions($this->value);
    }

    /**
     * Extrai hashtags (#tag).
     * @return array<string>
     */
    public function extractHashtags(): array
    {
        return $this->toolkit->extractHashtags($this->value);
    }

    /**
     * Retorna o comprimento da string.
     */
    public function length(): int
    {
        return mb_strlen($this->value);
    }

    /**
     * Verifica se a string contém uma substring.
     */
    public function contains(string $needle): bool
    {
        return str_contains($this->value, $needle);
    }

    /**
     * Verifica se a string começa com uma substring.
     */
    public function startsWith(string $needle): bool
    {
        return str_starts_with($this->value, $needle);
    }

    /**
     * Verifica se a string termina com uma substring.
     */
    public function endsWith(string $needle): bool
    {
        return str_ends_with($this->value, $needle);
    }

    /**
     * Verifica se a string está vazia.
     */
    public function isEmpty(): bool
    {
        return empty($this->value);
    }

    /**
     * Verifica se a string não está vazia.
     */
    public function isNotEmpty(): bool
    {
        return !empty($this->value);
    }
}
