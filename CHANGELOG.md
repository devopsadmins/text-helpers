# Changelog

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.

O formato é baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/),
e este projeto adere ao [Semantic Versioning](https://semver.org/lang/pt-BR/).

## [2.0.0] - 2026-02-16

### 🚀 Added - Funcionalidades Profissionais

#### Interface Fluida (Method Chaining)
- Adicionada classe `FluentString` que permite encadear operações
- Helper `text()` agora aceita parâmetro opcional para retornar `FluentString`
- Suporte completo para operações encadeadas estilo Laravel
- Métodos auxiliares: `upper()`, `lower()`, `title()`, `trim()`, `replace()`, `limit()`
- Métodos de verificação: `contains()`, `startsWith()`, `endsWith()`, `isEmpty()`, `isNotEmpty()`, `length()`

Exemplo:
```php
text("  NOME  ")->clean()->formatName()->abbreviate(20);
```

#### Slug Customizado (`slugWithSpecialChars`)
- Cria slugs mantendo caracteres especiais especificados
- Útil para sistemas de menções (@usuario) e hashtags (#tag)
- Suporta separador customizado
- Ideal para SEO avançado e sistemas de marcação

Exemplo:
```php
text()->slugWithSpecialChars("@devops #laravel", ['@', '#'])
// Resultado: "@devops-#laravel"
```

#### Truncar HTML Inteligente (`truncateHtml`)
- Corta HTML sem quebrar tags
- Mantém estrutura válida do HTML
- Fecha tags corretamente usando DOMDocument
- Perfeito para resumos de posts de editores WYSIWYG

Exemplo:
```php
text()->truncateHtml('<p>Texto <strong>longo</strong></p>', 10)
```

#### Destacar Palavras-chave (`highlight`)
- Envolve termos de busca em tags HTML
- Case-insensitive mas mantém case original
- Suporta múltiplas palavras
- Tag HTML customizável (padrão: `<mark>`)

Exemplo:
```php
text()->highlight("Laravel é incrível", "laravel")
// Resultado: "O <mark>Laravel</mark> é incrível"
```

#### Gerenciamento de Emojis
- `stripEmojis()`: Remove emojis (útil para bancos sem utf8mb4)
- `emojify()`: Converte shortcodes em emojis

Shortcodes suportados:
- `:smile:` → 😀
- `:heart:` → ❤️
- `:fire:` → 🔥
- `:rocket:` → 🚀
- `:thumbsup:` → 👍
- E mais 12+ shortcodes

Exemplo:
```php
text()->stripEmojis("Olá! 😀")       // "Olá!"
text()->emojify("Olá :smile:")       // "Olá 😀"
```

#### Valor Monetário por Extenso (`moneyToWords`)
- Converte valores para extenso em português (Brasil)
- Suporta valores até 999.999.999,99
- Essencial para contratos, recibos e notas fiscais
- Tratamento correto de singular/plural

Exemplo:
```php
text()->moneyToWords(150.50)
// "cento e cinquenta reais e cinquenta centavos"
```

#### Extração de Menções e Hashtags
- `extractMentions()`: Extrai @menções de texto
- `extractHashtags()`: Extrai #hashtags de texto
- Retorna arrays únicos (sem duplicatas)
- Regex otimizada para performance

Exemplo:
```php
text()->extractMentions("Olá @joao e @maria")  // ['joao', 'maria']
text()->extractHashtags("#Laravel #PHP")        // ['Laravel', 'PHP']
```

#### Markdown para Texto Plano (`markdownToPlainText`)
- Remove toda formatação Markdown
- Limpa links, imagens, negrito, itálico
- Ideal para prévias de email ou meta descriptions
- Mantém apenas o texto limpo

Exemplo:
```php
text()->markdownToPlainText("# **Título**")  // "Título"
```

### 🔧 Changed
- Helper `text()` agora suporta dois modos:
  - `text()` - Retorna `StringToolkit` (acesso direto aos métodos)
  - `text("string")` - Retorna `FluentString` (interface fluida)

### 📚 Documentation
- README completamente reformulado
- Adicionada seção de Funcionalidades Avançadas
- Exemplos práticos de casos de uso reais
- Criado arquivo `EXAMPLES.php` com 50+ exemplos

### ✅ Tests
- Adicionados 15+ novos testes para as novas funcionalidades
- Criada suite de testes `FluentStringTest` com 20+ casos
- Cobertura completa de todas as novas features
- Testes de integração entre métodos encadeados

## [1.0.0] - Data Anterior

### Added
- Classe `StringToolkit` como readonly class (PHP 8.2+)
- Método `splitName()` - Separar nome e sobrenome
- Método `abbreviate()` - Abreviar nomes longos
- Método `formatName()` - Formatar nomes próprios (PT-BR)
- Método `mask()` - Mascarar dados sensíveis
- Método `initials()` - Extrair iniciais
- Método `clean()` - Limpar input
- Método `readTime()` - Estimar tempo de leitura
- Método `isClean()` - Verificar profanidade
- Método `shortId()` - Gerar IDs curtos
- Helper global `text()`
- ServiceProvider com auto-discovery
- Suite completa de testes

---

## Semântica de Versionamento

Este projeto segue o [Semantic Versioning](https://semver.org/):

- **MAJOR** (X.0.0): Mudanças incompatíveis com versões anteriores
- **MINOR** (0.X.0): Novas funcionalidades compatíveis
- **PATCH** (0.0.X): Correções de bugs compatíveis
