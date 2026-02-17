<?php

/**
 * Exemplos de Uso das Novas Funcionalidades
 * String Toolkit - devopsadmins/text-helpers
 */

// ============================================
// 1. INTERFACE FLUIDA (Method Chaining)
// ============================================

// Exemplo 1: Processamento de nome completo
echo text("  VINICIUS DIAS DE SOUZA  ")
    ->clean()
    ->formatName()
    ->abbreviate(20);
// Resultado: "Vinícius D. Souza"

// Exemplo 2: Slug customizado para redes sociais
echo text("@devops_admins #laravel")
    ->slugWithSpecialChars(['@', '#']);
// Resultado: "@devops-admins-#laravel"

// Exemplo 3: Processamento de conteúdo Markdown
echo text("# **João Da Silva**")
    ->markdownToPlainText()
    ->clean()
    ->formatName();
// Resultado: "João da Silva"

// ============================================
// 2. SLUG CUSTOMIZADO
// ============================================

// Manter @ e # em slugs (para menções e hashtags)
$slug = text()->slugWithSpecialChars("@usuario menciona #Laravel", ['@', '#']);
// Resultado: "@usuario-menciona-#laravel"

// Slug padrão
$slug = text("Hello World!")->slug();
// Resultado: "hello-world"

// Com separador customizado
$slug = text("API Documentation")->slugWithSpecialChars([], '_');
// Resultado: "api_documentation"

// ============================================
// 3. TRUNCAR HTML INTELIGENTE
// ============================================

$html = '<p>Este é um <strong>texto longo</strong> com <em>HTML</em> que precisa ser cortado sem quebrar tags.</p>';

$resumo = text()->truncateHtml($html, 30);
// Resultado: "<p>Este é um <strong>texto longo</strong>...</p>"

// Uso em blog
$previa = text($post->conteudo_html)
    ->truncateHtml(150, ' [continua...]')
    ->toString();

// ============================================
// 4. DESTACAR PALAVRAS-CHAVE (Busca)
// ============================================

// Destacar termo em resultado de busca
$texto = "O Laravel é um framework PHP incrível";
$resultado = text()->highlight($texto, "laravel");
// Resultado: "O <mark>Laravel</mark> é um framework PHP incrível"

// Múltiplas palavras
$resultado = text($texto)->highlight(["laravel", "php"], "mark");
// Resultado: "O <mark>Laravel</mark> é um framework <mark>PHP</mark> incrível"

// Tag customizada (para CSS específico)
$resultado = text($texto)->highlight("Laravel", "span");
// Resultado: "O <span>Laravel</span> é..."

// ============================================
// 5. GERENCIAR EMOJIS
// ============================================

// Remover emojis (para bancos sem utf8mb4)
$limpo = text("Olá! 😀 Tudo bem? 🚀")->stripEmojis();
// Resultado: "Olá! Tudo bem?"

// Converter shortcodes em emojis
$mensagem = text("Isso é :fire: demais! :thumbsup:")->emojify();
// Resultado: "Isso é 🔥 demais! 👍"

// Encadeado: adicionar emojis e depois remover
$processado = text("Olá :smile:")
    ->emojify()      // "Olá 😀"
    ->stripEmojis(); // "Olá "

// ============================================
// 6. VALOR MONETÁRIO POR EXTENSO
// ============================================

// Geração de contratos/recibos
echo text()->moneyToWords(150.50);
// Resultado: "cento e cinquenta reais e cinquenta centavos"

echo text()->moneyToWords(1.00);
// Resultado: "um real"

echo text()->moneyToWords(2000.10);
// Resultado: "dois mil reais e dez centavos"

echo text()->moneyToWords(1000000);
// Resultado: "um milhão reais"

// Exemplo real: Recibo
$valor = 1250.75;
$valorExtenso = text()->moneyToWords($valor);
echo "Recebi a quantia de {$valorExtenso}.";
// "Recebi a quantia de mil duzentos e cinquenta reais e setenta e cinco centavos."

// ============================================
// 7. EXTRAIR MENÇÕES E HASHTAGS
// ============================================

// Extrair menções de posts sociais
$post = "Olá @joao, você viu o que @maria postou? @joao vai adorar!";
$mentions = text()->extractMentions($post);
// Resultado: ['joao', 'maria'] (sem duplicatas)

// Extrair hashtags para indexação
$tweet = "Adoro #Laravel e #PHP! #Laravel é o melhor framework.";
$hashtags = text()->extractHashtags($tweet);
// Resultado: ['Laravel', 'PHP'] (sem duplicatas)

// Modo fluido
$mentions = text($post)->extractMentions();
$hashtags = text($tweet)->extractHashtags();

// Uso prático: Sistema de notificações
$comentario = "@admin Preciso de ajuda com @suporte";
$notificar = text($comentario)->extractMentions();
foreach ($notificar as $username) {
    // Enviar notificação para usuário
    // notifyUser($username);
}

// ============================================
// 8. MARKDOWN PARA TEXTO PLANO
// ============================================

$markdown = <<<MD
# Título Principal

Este é um **texto** em _markdown_ com [link](http://example.com) 
e ![imagem](img.png).

## Subtítulo
- Item 1
- Item 2
MD;

$textoPlano = text()->markdownToPlainText($markdown);
// Remove todas as marcações Markdown

// Uso prático: Prévia de email (texto alternativo)
$previewEmail = text($artigo->conteudo_markdown)
    ->markdownToPlainText()
    ->limit(100)
    ->toString();

// ============================================
// 9. MÉTODOS AUXILIARES DA INTERFACE FLUIDA
// ============================================

// Transformação de case
$upper = text("hello")->upper()->get();                    // "HELLO"
$lower = text("HELLO")->lower()->get();                    // "hello"
$title = text("hello world")->title()->get();              // "Hello World"

// Manipulação de string
$trimmed = text("  spaces  ")->trim()->get();              // "spaces"
$replaced = text("Hello World")->replace("World", "Laravel")->get(); // "Hello Laravel"
$limited = text("Long text here")->limit(8, '...')->get(); // "Long tex..."

// Verificações (retornam valores, não são encadeáveis)
$contains = text("Hello World")->contains("World");        // true
$starts = text("Hello")->startsWith("Hel");                // true
$ends = text("Hello")->endsWith("lo");                     // true
$empty = text("")->isEmpty();                              // true
$notEmpty = text("Hello")->isNotEmpty();                   // true
$length = text("Hello")->length();                         // 5

// ============================================
// 10. CASOS DE USO REAIS
// ============================================

// Formulário de Cadastro
function processarNome($nomeInput) {
    return text($nomeInput)
        ->clean()           // Remove espaços extras
        ->formatName()      // Formata corretamente
        ->abbreviate(30)    // Abrevia se muito longo
        ->toString();
}

// Sistema de Blog
function gerarMetadados($post) {
    return [
        'tempo_leitura' => text($post->conteudo)->readTime() . ' min',
        'resumo' => text($post->conteudo_html)->truncateHtml(200),
        'mentions' => text($post->conteudo)->extractMentions(),
        'hashtags' => text($post->extractHashtags($post->conteudo),
        'preview_email' => text($post->markdown)->markdownToPlainText()->limit(150),
    ];
}

// Geração de Documentos Fiscais
function gerarRecibo($valor, $beneficiario) {
    $valorExtenso = text()->moneyToWords($valor);
    $nomeFormatado = text($beneficiario)->formatName();
    
    return "Recebi de {$nomeFormatado} a quantia de {$valorExtenso}.";
}

// Sistema de Busca
function destacarResultados($resultados, $termoBusca) {
    return array_map(function($item) use ($termoBusca) {
        return [
            'titulo' => text($item->titulo)->highlight($termoBusca),
            'descricao' => text($item->descricao)->highlight($termoBusca),
        ];
    }, $resultados);
}

// ============================================
// 11. COMBINAÇÕES AVANÇADAS
// ============================================

// Pipeline completo de processamento
$textoFinal = text($input)
    ->clean()                          // 1. Limpa sujeira
    ->stripEmojis()                    // 2. Remove emojis
    ->markdownToPlainText()            // 3. Remove markdown
    ->formatName()                     // 4. Formata como nome
    ->abbreviate(25)                   // 5. Abrevia se necessário
    ->mask(15)                         // 6. Mascara se necessário
    ->toString();

// Preparar conteúdo para diferentes canais
$conteudo = "# Título\n\n**Importante**: Entre em contato @admin! 🚀";

$paraEmail = text($conteudo)
    ->markdownToPlainText()
    ->stripEmojis()
    ->toString();

$paraDB = text($conteudo)
    ->stripEmojis()                    // Remove emojis
    ->clean()                          // Limpa espaços
    ->toString();

$paraBusca = text($conteudo)
    ->markdownToPlainText()
    ->lower()                          // Normaliza para busca
    ->toString();

$mentions = text($conteudo)->extractMentions();  // ['admin']
