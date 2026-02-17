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
```

#### 3. Formatar Nomes Próprios (`formatName`)

Converte nomes para *Title Case*, respeitando preposições em português (`de`, `da`, `dos`, `e`, etc.) que devem permanecer em minúsculo.

```php
echo text()->formatName("VINICIUS DE SOUZA");
// Saída: "Vinícius de Souza"
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
```

#### 5. Extração de Iniciais (`initials`)

Gera iniciais a partir de um nome, ideal para avatares (User Interface).

```php
echo text()->initials("Vinícius Dias de Souza");
// Saída: "VS"
```

#### 6. Limpeza de Input (`clean`)

Remove caracteres invisíveis, espaços duplos, tabs e quebras de linha desnecessárias. Essencial para higienizar dados colados de PDFs ou planilhas.

```php
echo text()->clean("  Nome    Sobrenome  ");
// Saída: "Nome Sobrenome"
```

#### 7. Estimativa de Tempo de Leitura (`readTime`)

Calcula o tempo estimado de leitura em minutos, baseado em uma média de palavras por minuto (padrão: 200 PPM).

```php
$minutes = text()->readTime($conteudoLongo);
echo "$minutes min de leitura";
```

#### 8. Verificador de Termos Ofensivos (`isClean`)

Verifica se o texto contém termos de uma lista de bloqueio (profanity filter).

```php
if (! text()->isClean($comentario)) {
    abort(403, "Conteúdo inadequado.");
}
```

#### 9. IDs Curtos Legíveis (`shortId`)

Gera identificadores únicos curtos e amigáveis para humanos (sem caracteres ambíguos como `0`, `O`, `1`, `l`). Ideal para URLs curtas ou códigos de cupom.

```php
echo text()->shortId(6);
// Saída Ex: "K9P3XZ"
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
