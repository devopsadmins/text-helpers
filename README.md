# String ToolKit for Arbe

Biblioteca de manipulação de strings de alta performance, otimizada para o ecossistema Laravel e desenvolvida com foco em segurança de tipos (`readonly class`) e eficiência.

## 📋 Requisitos

- **PHP**: ^8.2
- **Laravel**: ^10.0 / ^11.0 / ^12.0

## 🚀 Instalação

Instale o pacote via Composer:

```bash
composer require devopsadmins/text-helpers
```

> **Autodiscovery**: O Laravel registrará automaticamente o `TextHelperServiceProvider`.

## 🛠 Como Usar

O pacote oferece duas formas de uso:

1. **Acesso Direto**: `text()->método()`  
2. **Interface Fluida (Method Chaining)**: `text("valor")->método1()->método2()`

### Interface Fluida (Recomendado)

A interface fluida permite encadear múltiplas operações de forma elegante e legível:

```php
// Exemplo: Limpa, formata e abrevia um nome
echo text("  VINICIUS DIAS DE SOUZA  ")
    ->clean()
    ->formatName()
    ->abbreviate(20);
// Resultado: "Vinícius D. Souza"

// Exemplo: Processa um slug customizado
echo text("@devops_admins #laravel")
    ->slugWithSpecialChars(['@', '#']);
// Resultado: "@devops-admins-#laravel"

// Exemplo: Converte markdown e formata
echo text("# **João Da Silva**")
    ->markdownToPlainText()
    ->formatName();
// Resultado: "João da Silva"
```

### Helper Global `text()`

A maneira mais rápida de acessar as ferramentas é através do helper global `text()`.

#### 1. Separar Nome e Sobrenome (`splitName`)

Ideal para normalizar inputs de cadastro ou saídas de API.

```php
$fullName = "Vinícius Dias de Souza";

$data = text()->splitName($fullName);

// Resultado:
// [
//     'firstName' => 'Vinícius',
//     'lastName'  => 'Dias de Souza'
// ]

echo $data['firstName']; // Vinícius

// Modo fluido
$data = text("Vinícius Dias de Souza")->splitName();
```

#### 2. Abreviar Nomes Longos (`abbreviate`)

Formata nomes para caber em layouts restritos (listagens, boletos, crachás), preservando o primeiro e último nome.

```php
// Abrevia se for maior que o limite (padrão 20 chars)
echo text()->abbreviate("Vinícius Dias de Souza"); 
// Saída: "Vinícius D. Souza"

// Com limite personalizado
echo text()->abbreviate("Maria da Silva", 50); 
// Saída: "Maria da Silva" (não abrevia pois cabe no limite)

// Modo fluido
echo text("Vinícius Dias de Souza")->abbreviate(20);
```

#### 3. Formatar Nomes Próprios (`formatName`)

Converte nomes para *Title Case*, respeitando preposições em português (`de`, `da`, `dos`, `e`, etc.) que devem permanecer em minúsculo.

```php
echo text()->formatName("VINICIUS DE SOUZA");
// Saída: "Vinícius de Souza"

// Modo fluido
echo text("VINICIUS DE SOUZA")->formatName();
```

#### 4. Mascarar Dados Sensíveis (`mask`)

Ofusca partes de uma string. Detecta automaticamente e-mails para mascarar tanto o usuário quanto o domínio.

```php
// Email
echo text()->mask("everton@gmail.com");
// Saída: "eve*****@g****.com"

// CPF / Outros (mantém os primeiros X caracteres visíveis)
echo text()->mask("12345678900", 3);
// Saída: "123********"

// Modo fluido
echo text("everton@gmail.com")->mask();
```

#### 5. Extração de Iniciais (`initials`)

Gera iniciais a partir de um nome, ideal para avatares (User Interface).

```php
echo text()->initials("Vinícius Dias de Souza");
// Saída: "VS"

// Modo fluido
echo text("Vinícius Dias de Souza")->initials();
```

#### 6. Limpeza de Input (`clean`)

Remove caracteres invisíveis, espaços duplos, tabs e quebras de linha desnecessárias. Essencial para higienizar dados colados de PDFs ou planilhas.

```php
echo text()->clean("  Nome    Sobrenome  ");
// Saída: "Nome Sobrenome"

// Modo fluido
echo text("  Nome    Sobrenome  ")->clean();
```

#### 7. Estimativa de Tempo de Leitura (`readTime`)

Calcula o tempo estimado de leitura em minutos, baseado em uma média de palavras por minuto (padrão: 200 PPM).

```php
$minutes = text()->readTime($conteudoLongo);
echo "$minutes min de leitura";

// Modo fluido
$minutes = text($conteudoLongo)->readTime();
```

#### 8. Verificador de Termos Ofensivos (`isClean`)

Verifica se o texto contém termos de uma lista de bloqueio (profanity filter).

```php
if (! text()->isClean($comentario)) {
    abort(403, "Conteúdo inadequado.");
}

// Modo fluido
if (! text($comentario)->isClean()) {
    abort(403, "Conteúdo inadequado.");
}
```

#### 9. IDs Curtos Legíveis (`shortId`)

Gera identificadores únicos curtos e amigáveis para humanos (sem caracteres ambíguos como `0`, `O`, `1`, `l`). Ideal para URLs curtas ou códigos de cupom.

```php
echo text()->shortId(6);
// Saída Ex: "K9P3XZ"
```

## 🔥 Funcionalidades Avançadas

### 10. Slug Customizado (`slugWithSpecialChars`)

Cria slugs mantendo caracteres especiais especificados. Perfeito para sistemas de menções e hashtags.

```php
echo text()->slugWithSpecialChars("@devops_admins #laravel", ['@', '#']);
// Saída: "@devops-admins-#laravel"

// Slug padrão
echo text("Hello World!")->slug();
// Saída: "hello-world"

// Com separador customizado
echo text("Hello World")->slugWithSpecialChars([], '_');
// Saída: "hello_world"
```

### 11. Truncar HTML Inteligente (`truncateHtml`)

Corta HTML sem quebrar tags, mantendo a estrutura válida.

```php
$html = '<p>Este é um <strong>texto longo</strong> com HTML.</p>';

echo text()->truncateHtml($html, 20);
// Saída: "<p>Este é um <strong>texto</strong>...</p>"

// Modo fluido
echo text($html)->truncateHtml(20, '...');
```

### 12. Destacar Palavras-chave (`highlight`)

Envolve termos de busca em tags HTML (padrão: `<mark>`), mantendo o case original.

```php
echo text()->highlight("O Laravel é incrível", "laravel");
// Saída: "O <mark>Laravel</mark> é incrível"

// Múltiplas palavras
echo text()->highlight("Laravel e PHP", ["laravel", "php"]);
// Saída: "<mark>Laravel</mark> e <mark>PHP</mark>"

// Tag customizada
echo text("Laravel")->highlight("laravel", "span");
// Saída: "<span>Laravel</span>"
```

### 13. Gerenciar Emojis

#### Remover Emojis (`stripEmojis`)
Útil para bancos de dados que não suportam `utf8mb4`.

```php
echo text()->stripEmojis("Olá! 😀");
// Saída: "Olá!"

// Modo fluido
echo text("Olá! 😀")->stripEmojis();
```

#### Converter Shortcodes (`emojify`)

```php
echo text()->emojify("Isso é :fire: demais :thumbsup:");
// Saída: "Isso é 🔥 demais 👍"

// Modo fluido
echo text("Hello :smile:")->emojify();
// Saída: "Hello 😀"
```

Shortcodes suportados: `:smile:`, `:heart:`, `:fire:`, `:thumbsup:`, `:rocket:`, `:party:`, entre outros.

### 14. Valor Monetário por Extenso (`moneyToWords`)

Converte valores monetários para extenso em português (Brasil). Essencial para contratos, recibos e documentos fiscais.

```php
echo text()->moneyToWords(150.50);
// Saída: "cento e cinquenta reais e cinquenta centavos"

echo text()->moneyToWords(1.00);
// Saída: "um real"

echo text()->moneyToWords(2000.10);
// Saída: "dois mil reais e dez centavos"

echo text()->moneyToWords(1000000);
// Saída: "um milhão reais"
```

### 15. Extrair Menções e Hashtags

#### Extrair Menções (`extractMentions`)

```php
$text = "Olá @usuario1, você viu o que @usuario2 postou? @usuario1 está incrível!";
$mentions = text()->extractMentions($text);
// Resultado: ['usuario1', 'usuario2'] (sem duplicatas)

// Modo fluido
$mentions = text($text)->extractMentions();
```

#### Extrair Hashtags (`extractHashtags`)

```php
$text = "Adoro #Laravel e #PHP! #Laravel é o melhor.";
$hashtags = text()->extractHashtags($text);
// Resultado: ['Laravel', 'PHP'] (sem duplicatas)

// Modo fluido
$hashtags = text($text)->extractHashtags();
```

### 16. Markdown para Texto Plano (`markdownToPlainText`)

Remove toda formatação Markdown, ideal para prévias de e-mail ou descrições meta.

```php
$markdown = '# Título\n\nEste é um **texto** em _markdown_ com [link](http://example.com).';

echo text()->markdownToPlainText($markdown);
// Saída: "Título\n\nEste é um texto em markdown com link."

// Modo fluido
echo text($markdown)->markdownToPlainText();
```

## 🔗 Métodos Adicionais da Interface Fluida

A classe `FluentString` também oferece métodos auxiliares para manipulação comum:

```php
// Case transformation
text("hello")->upper()              // "HELLO"
text("HELLO")->lower()              // "hello"
text("hello world")->title()        // "Hello World"

// String manipulation
text("  spaces  ")->trim()          // "spaces"
text("Hello World")->replace("World", "Laravel")  // "Hello Laravel"
text("Long text...")->limit(5)      // "Long ..."

// Verificações (retornam bool)
text("Hello World")->contains("World")     // true
text("Hello")->startsWith("Hel")          // true
text("Hello")->endsWith("lo")             // true
text("")->isEmpty()                       // true
text("Hello")->isNotEmpty()               // true
text("Hello")->length()                   // 5
```

## 💡 Exemplos Práticos de Uso

### Processamento de Formulário

```php
$nomeFormatado = text($request->input('nome'))
    ->clean()
    ->formatName()
    ->toString();
```

### Sistema de Blog

```php
// Tempo de leitura e resumo
$tempoLeitura = text($post->conteudo)->readTime();
$resumo = text($post->conteudo_html)->truncateHtml(150);

// Extração de metadados
$mentions = text($post->conteudo)->extractMentions();
$hashtags = text($post->conteudo)->extractHashtags();
```

### Geração de Documentos

```php
$valorExtenso = text()->moneyToWords($invoice->total);
// "mil duzentos e trinta reais e quarenta e cinco centavos"
```

### Sistema de Busca

```php
$resultadoDestacado = text($conteudo)->highlight($termoBusca, 'mark');
```

### Injeção de Dependência

Se preferir não usar helpers globais, você pode injetar o serviço diretamente em Controllers, Jobs ou outros Serviços.

```php
use Arbe\TextHelpers\Services\StringToolkit;

class CustomerController extends Controller
{
    public function __construct(
        private readonly StringToolkit $text
    ) {}

    public function store(Request $request)
    {
        $name = $this->text->abbreviate($request->input('name'));
        
        // ...
    }
}
```

## ✅ Testes e Qualidade

O pacote inclui uma suíte de testes automatizados utilizando **PHPUnit** e **Orchestra Testbench**, cobrindo 100% das funcionalidades críticas.

### Executando os Testes

Para rodar a bateria de testes localmente:

```bash
composer test
```

### Estrutura

- **Unitários** (`tests/Unit`): Valida cada método da classe `StringToolkit` isoladamente.
- **Integração** (`tests/Feature`): Garante que o pacote se comporta corretamente dentro do container Laravel.

### Automação (Git Hook)

Para prevenir erros em produção, este repositório conta com um hook de **pre-push**. 
Sempre que você executar `git push`, os testes serão rodados automaticamente. Se algum teste falhar, o envio é bloqueado.

> O script de verificação encontra-se em `.git/hooks/pre-push`.

## ⚙️ Características Técnicas

- **Imutabilidade**: Implementado como `readonly class` do PHP 8.2+, prevenindo modificações acidentais de estado.
- **UTF-8 Safe**: Utiliza funções `mb_*` para garantir o tratamento correto de acentos e caracteres especiais.
- **Performance**: Otimizado para baixo consumo de memória, registrado como Singleton no container do Laravel.

## 📝 Licença

MIT License.
