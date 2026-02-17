<?php

namespace Tests\Unit;

use Tests\TestCase;
use Arbe\TextHelpers\Services\FluentString;
use Arbe\TextHelpers\Services\StringToolkit;

class FluentStringTest extends TestCase
{
    /** @test */
    public function it_can_be_created_from_text_helper()
    {
        $fluent = text('Hello World');
        
        $this->assertInstanceOf(FluentString::class, $fluent);
        $this->assertEquals('Hello World', $fluent->toString());
    }

    /** @test */
    public function it_supports_method_chaining()
    {
        $result = text('  VINÍCIUS DIAS DE SOUZA  ')
            ->clean()
            ->formatName()
            ->abbreviate(20)
            ->toString();

        $this->assertEquals('Vinícius D. Souza', $result);
    }

    /** @test */
    public function it_can_be_cast_to_string()
    {
        $fluent = text('Hello')->upper();
        
        $this->assertEquals('HELLO', (string) $fluent);
        $this->assertEquals('HELLO', $fluent);
    }

    /** @test */
    public function clean_method_works_fluently()
    {
        $result = text("  Nome    Sobrenome  \n\t")->clean()->get();
        
        $this->assertEquals('Nome Sobrenome', $result);
    }

    /** @test */
    public function format_name_works_fluently()
    {
        $result = text('VINÍCIUS DE SOUZA')->formatName()->get();
        
        $this->assertEquals('Vinícius de Souza', $result);
    }

    /** @test */
    public function abbreviate_works_fluently()
    {
        $result = text('Vinícius Dias de Souza')->abbreviate(20)->get();
        
        $this->assertEquals('Vinícius D. Souza', $result);
    }

    /** @test */
    public function mask_works_fluently()
    {
        $result = text('12345678900')->mask(3)->get();
        
        $this->assertEquals('123********', $result);
    }

    /** @test */
    public function slug_methods_work_fluently()
    {
        $result = text('Hello World')->slug()->get();
        $this->assertEquals('hello-world', $result);

        $result = text('@devops_admins #laravel')
            ->slugWithSpecialChars(['@', '#'])
            ->get();
        
        $this->assertEquals('@devops-admins-#laravel', $result);
    }

    /** @test */
    public function truncate_html_works_fluently()
    {
        $result = text('<p>Long text here</p>')->truncateHtml(5)->get();
        
        $this->assertStringContainsString('...', $result);
    }

    /** @test */
    public function highlight_works_fluently()
    {
        $result = text('Laravel é incrível')->highlight('Laravel')->get();
        
        $this->assertStringContainsString('<mark>Laravel</mark>', $result);
    }

    /** @test */
    public function emoji_methods_work_fluently()
    {
        $result = text('Olá! 😀')->stripEmojis()->get();
        $this->assertEquals('Olá!', $result);

        $result = text('Hello :smile:')->emojify()->get();
        $this->assertStringContainsString('😀', $result);
    }

    /** @test */
    public function markdown_to_plain_text_works_fluently()
    {
        $result = text('# Title\n**bold**')->markdownToPlainText()->get();
        
        $this->assertStringNotContainsString('#', $result);
        $this->assertStringNotContainsString('**', $result);
    }

    /** @test */
    public function case_methods_work_fluently()
    {
        $this->assertEquals('HELLO', text('hello')->upper()->get());
        $this->assertEquals('hello', text('HELLO')->lower()->get());
        $this->assertEquals('Hello World', text('hello world')->title()->get());
    }

    /** @test */
    public function trim_works_fluently()
    {
        $result = text('  spaces  ')->trim()->get();
        
        $this->assertEquals('spaces', $result);
    }

    /** @test */
    public function replace_works_fluently()
    {
        $result = text('Hello World')->replace('World', 'Laravel')->get();
        
        $this->assertEquals('Hello Laravel', $result);
    }

    /** @test */
    public function limit_works_fluently()
    {
        $result = text('This is a long text')->limit(10)->get();
        
        $this->assertEquals('This is a ...', $result);
        
        $result = text('Short')->limit(10)->get();
        $this->assertEquals('Short', $result);
    }

    /** @test */
    public function terminal_methods_return_values()
    {
        // splitName returns array
        $result = text('Vinícius Souza')->splitName();
        $this->assertIsArray($result);
        $this->assertEquals('Vinícius', $result['firstName']);

        // initials returns string
        $this->assertEquals('VS', text('Vinícius Souza')->initials());

        // readTime returns int
        $this->assertIsInt(text('Some text here')->readTime());

        // isClean returns bool
        $this->assertTrue(text('Clean text')->isClean());

        // extractMentions returns array
        $this->assertIsArray(text('@user here')->extractMentions());

        // extractHashtags returns array
        $this->assertIsArray(text('#tag here')->extractHashtags());

        // length returns int
        $this->assertEquals(5, text('Hello')->length());

        // contains returns bool
        $this->assertTrue(text('Hello World')->contains('World'));

        // startsWith returns bool
        $this->assertTrue(text('Hello')->startsWith('Hel'));

        // endsWith returns bool
        $this->assertTrue(text('Hello')->endsWith('llo'));

        // isEmpty returns bool
        $this->assertFalse(text('Hello')->isEmpty());
        $this->assertTrue(text('')->isEmpty());

        // isNotEmpty returns bool
        $this->assertTrue(text('Hello')->isNotEmpty());
        $this->assertFalse(text('')->isNotEmpty());
    }

    /** @test */
    public function complex_chaining_example()
    {
        // Simula um caso de uso real
        $result = text('  **João Da Silva**  ')
            ->markdownToPlainText()
            ->clean()
            ->formatName()
            ->toString();

        $this->assertEquals('João da Silva', $result);
    }

    /** @test */
    public function can_chain_with_laravel_style()
    {
        // Exemplo que imita o estilo do Laravel
        $result = text('  dirty input  ')
            ->trim()
            ->upper()
            ->replace('DIRTY', 'CLEAN')
            ->get();

        $this->assertEquals('CLEAN INPUT', $result);
    }
}
