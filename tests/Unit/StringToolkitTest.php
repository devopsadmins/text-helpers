<?php

namespace Tests\Unit;

use Tests\TestCase;
use Arbe\TextHelpers\Services\StringToolkit;

class StringToolkitTest extends TestCase
{
    private StringToolkit $toolkit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->toolkit = new StringToolkit();
    }

    /** @test */
    public function it_can_be_resolved_via_helper()
    {
        $this->assertInstanceOf(StringToolkit::class, text());
    }

    /** @test */
    public function it_splits_names_correctly()
    {
        $result = $this->toolkit->splitName('Vinícius Dias de Souza');

        $this->assertEquals('Vinícius', $result['firstName']);
        $this->assertEquals('Dias de Souza', $result['lastName']);

        // Single name
        $result = $this->toolkit->splitName('Vinícius');
        $this->assertEquals('Vinícius', $result['firstName']);
        $this->assertEmpty($result['lastName']);
    }

    /** @test */
    public function it_abbreviates_names()
    {
        // Should abbreviate middle names
        $this->assertEquals(
            'Vinícius D. Souza',
            $this->toolkit->abbreviate('Vinícius Dias de Souza')
        );

        // Should update limit
        $this->assertEquals(
            'Maria da Silva',
            $this->toolkit->abbreviate('Maria da Silva', 50)
        );
        
        // Should ignore prepositions
        $this->assertEquals(
            'Vinícius D. Souza',
            $this->toolkit->abbreviate('Vinícius Dias de Souza')
        );
    }

    /** @test */
    public function it_formats_names_title_case_with_ptbr_prepositions()
    {
        $this->assertEquals(
            'Vinícius de Souza',
            $this->toolkit->formatName('VINÍCIUS DE SOUZA')
        );

        $this->assertEquals(
            'Maria da Silva e Santos',
            $this->toolkit->formatName('MARIA DA SILVA E SANTOS')
        );

        $this->assertEquals(
            'João dos Passos',
            $this->toolkit->formatName('joão dos passos')
        );
    }

    /** @test */
    public function it_masks_emails_correctly()
    {
        // Testing eolimabr@gmail.com -> eol*****@g****.com (default 3 chars)
        $this->assertEquals(
            'eol*****@g****.com',
            $this->toolkit->mask('eolimabr@gmail.com')
        );

        // Short local part (local not masked, domain masked)
        $this->assertEquals(
            'ab@d*****.com',
            $this->toolkit->mask('ab@domain.com', 3)
        );

        // Short domain part (masked local, unmasked domain)
        $this->assertEquals(
            'use*@a.com',
            $this->toolkit->mask('user@a.com', 3)
        );
    }

    /** @test */
    public function it_masks_generic_strings()
    {
        // CPF-like
        $this->assertEquals(
            '123********',
            $this->toolkit->mask('12345678900', 3)
        );

        // Short string
        $this->assertEquals(
            '123',
            $this->toolkit->mask('123', 5)
        );
    }

    /** @test */
    public function it_generates_initials()
    {
        $this->assertEquals(
            'VS',
            $this->toolkit->initials('Vinícius Dias de Souza')
        );

        $this->assertEquals(
            'V',
            $this->toolkit->initials('Vinícius', 1)
        );

        $this->assertEquals(
            'VS', 
            $this->toolkit->initials('Vinícius de Souza')
        );
    }

    /** @test */
    public function it_cleans_dirty_input()
    {
        $dirty = "  Nome    Sobrenome  \n\t";
        $this->assertEquals(
            'Nome Sobrenome',
            $this->toolkit->clean($dirty)
        );
    }

    /** @test */
    public function it_estimates_read_time()
    {
        // 200 words at 200 wpm = 1 min
        $text = str_repeat('palavra ', 200);
        $this->assertEquals(1, $this->toolkit->readTime($text));

        // 600 words at 200 wpm = 3 min
        $text = str_repeat('palavra ', 600);
        $this->assertEquals(3, $this->toolkit->readTime($text));
    }

    /** @test */
    public function it_checks_for_profanity()
    {
        $this->assertFalse(
            $this->toolkit->isClean('Texto com palavrao no meio')
        );

        $this->assertTrue(
            $this->toolkit->isClean('Texto limpo e seguro')
        );
        
        // Custom dictionary
        $this->assertFalse(
             $this->toolkit->isClean('Banana', ['banana'])
        );
    }

    /** @test */
    public function it_generates_short_ids()
    {
        $id = $this->toolkit->shortId(6);
        
        $this->assertEquals(6, strlen($id));
        $this->assertDoesNotMatchRegularExpression('/[0O1Il]/', $id);
    }

    /** @test */
    public function it_creates_slug_with_special_chars()
    {
        $this->assertEquals(
            '@devops-admins-#laravel',
            $this->toolkit->slugWithSpecialChars('@devops_admins #laravel', ['@', '#'])
        );

        // Standard slug (no special chars)
        $this->assertEquals(
            'hello-world',
            $this->toolkit->slugWithSpecialChars('Hello World!')
        );

        // With underscore separator
        $this->assertEquals(
            'hello_world',
            $this->toolkit->slugWithSpecialChars('Hello World', [], '_')
        );
    }

    /** @test */
    public function it_truncates_html_safely()
    {
        $html = '<p>Este é um <strong>texto longo</strong> com HTML que precisa ser cortado sem quebrar tags.</p>';
        
        $result = $this->toolkit->truncateHtml($html, 20);
        
        // Should not have broken tags
        $this->assertStringNotContainsString('<strong', $result);
        $this->assertStringContainsString('...', $result);
        
        // Short HTML should not be truncated
        $short = '<p>Curto</p>';
        $this->assertEquals($short, $this->toolkit->truncateHtml($short, 100));
    }

    /** @test */
    public function it_highlights_keywords()
    {
        $text = 'O Laravel é incrível';
        $result = $this->toolkit->highlight($text, 'laravel');
        
        $this->assertStringContainsString('<mark>Laravel</mark>', $result);
        
        // Multiple keywords
        $result = $this->toolkit->highlight($text, ['laravel', 'incrível']);
        $this->assertStringContainsString('<mark>Laravel</mark>', $result);
        $this->assertStringContainsString('<mark>incrível</mark>', $result);
        
        // Custom tag
        $result = $this->toolkit->highlight($text, 'Laravel', 'span');
        $this->assertStringContainsString('<span>Laravel</span>', $result);
    }

    /** @test */
    public function it_strips_emojis()
    {
        $this->assertEquals(
            'Olá!',
            $this->toolkit->stripEmojis('Olá! 😀')
        );

        $this->assertEquals(
            'Texto sem emojis',
            $this->toolkit->stripEmojis('Texto sem emojis')
        );
    }

    /** @test */
    public function it_converts_shortcodes_to_emojis()
    {
        $this->assertEquals(
            'Olá 😀',
            $this->toolkit->emojify('Olá :smile:')
        );

        $this->assertEquals(
            'Isso é 🔥',
            $this->toolkit->emojify('Isso é :fire:')
        );
    }

    /** @test */
    public function it_converts_money_to_words()
    {
        $this->assertEquals(
            'cento e cinquenta reais e cinquenta centavos',
            $this->toolkit->moneyToWords(150.50)
        );

        $this->assertEquals(
            'um real',
            $this->toolkit->moneyToWords(1.00)
        );

        $this->assertEquals(
            'zero reais',
            $this->toolkit->moneyToWords(0)
        );

        $this->assertEquals(
            'dois mil reais e dez centavos',
            $this->toolkit->moneyToWords(2000.10)
        );

        $this->assertEquals(
            'um milhão reais',
            $this->toolkit->moneyToWords(1000000)
        );
    }

    /** @test */
    public function it_extracts_mentions()
    {
        $text = 'Olá @usuario1, você viu o que @usuario2 postou? @usuario1 está incrível!';
        $mentions = $this->toolkit->extractMentions($text);
        
        $this->assertCount(2, $mentions);
        $this->assertContains('usuario1', $mentions);
        $this->assertContains('usuario2', $mentions);
    }

    /** @test */
    public function it_extracts_hashtags()
    {
        $text = 'Adoro #Laravel e #PHP! #Laravel é o melhor.';
        $hashtags = $this->toolkit->extractHashtags($text);
        
        $this->assertCount(2, $hashtags);
        $this->assertContains('Laravel', $hashtags);
        $this->assertContains('PHP', $hashtags);
    }

    /** @test */
    public function it_converts_markdown_to_plain_text()
    {
        $markdown = '# Título\n\nEste é um **texto** em _markdown_ com [link](http://example.com) e ![imagem](img.png).';
        
        $plain = $this->toolkit->markdownToPlainText($markdown);
        
        $this->assertStringNotContainsString('#', $plain);
        $this->assertStringNotContainsString('**', $plain);
        $this->assertStringNotContainsString('[', $plain);
        $this->assertStringContainsString('Título', $plain);
        $this->assertStringContainsString('texto', $plain);
        $this->assertStringContainsString('link', $plain);
    }
}

