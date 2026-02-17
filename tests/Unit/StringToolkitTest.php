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
}
