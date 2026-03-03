# VIP (Vox in Progress) - Site Vocal

Um site moderno para um grupo vocal, construído em **PHP puro** sem frameworks, com **Bootstrap 5**, **JSON** como banco de dados e pronto para hospedagem compartilhada.

## Características

✅ **PHP 8+** compatível  
✅ **Zero dependências** (sem Composer/npm necessário)  
✅ **Bootstrap 5** via CDN  
✅ **Bootstrap Icons** para ícones  
✅ **JSON** como armazenamento de dados  
✅ **Design responsivo** e moderno  
✅ **SEO-friendly**  
✅ **Segurança** com validação e escape de dados  
✅ **Roteamento simples** via querystring  
✅ **Formulário de contato** com validação  

## Estrutura de Pastas

```
vip/
├── index.php              # Router principal
├── app/
│   ├── config.php        # Configurações gerais
│   ├── helpers.php       # Funções auxiliares
│   ├── data/
│   │   ├── albums.json
│   │   ├── arranjos.json
│   │   ├── cantores.json
│   │   ├── agenda.json
│   │   └── historia.html
│   └── views/
│       ├── layout/
│       │   ├── header.php
│       │   ├── nav.php
│       │   └── footer.php
│       └── pages/
│           ├── home.php
│           ├── arranjos.php
│           ├── arranjo.php
│           ├── historia.php
│           ├── cantores.php
│           ├── cantor.php
│           ├── agenda.php
│           ├── contato.php
│           └── 404.php
└── assets/
    ├── css/
    │   └── site.css
    └── img/
```

## Como Usar Localmente

### Opção 1: PHP Built-in Server (PHP 5.4+)

```bash
cd site/vip
php -S localhost:8000
```

Depois abra no navegador: `http://localhost:8000`

### Opção 2: XAMPP, WAMP ou similar

1. Copie a pasta `vip` para `htdocs/` (XAMPP) ou `www/` (WAMP)
2. Acesse: `http://localhost/vip`

### Opção 3: Hospedagem Compartilhada

1. Upload de toda a pasta `vip/` via FTP
2. Acesse o domínio (ex.: `voxinprogress.vip/vip`)

## Configuração

Edite `app/config.php` para customizar:

```php
define('SITE_NAME', 'Vox in Progress');
define('SITE_TAGLINE', 'Arranjos e Performances Vocais');
define('CONTACT_EMAIL', 'seu@email.com');
define('ENABLE_EMAIL', false); // Ative para usar mail()
```

## Dados

### albums.json
Álbuns/coleções de arranjos. É possível também especificar uma capa de álbum
usando o campo `image`; o comportamento é idêntico ao dos arranjos (veja
`arranjos.json` abaixo). Em instalações que guardam as capas junto com outros
arquivos de acervo, basta fornecer um caminho relativo ou apenas o nome do
arquivo.

```json
[
  {
    "id": "insanity",
    "titulo": "Insanity",
    "descricao": "Arranjos de rock clássico",
    "ordem": 1,
    "image": "insanity_cover.jpg"  
  }
]
```
### arranjos.json
Detalhes dos arranjos com links para arquivos. Se desejar uma imagem de capa ela
pode ser fornecida no campo `image`.

O valor de `image` aceita dois formatos:

* caminho relativo a `assets/` (ex.: "img/music/foo.jpg") – mantém o comportamento
  anterior;
* nome de arquivo ou caminho relativo dentro do respectivo diretório do acervo
  (ex.: "cover.jpg", "folder/mini.png"). Neste caso a imagem é servida de
  `ACERVO_BASE_URL/<storagePath>/…`.

  `ACERVO_BASE_URL` é configurado dinamicamente em `app/config.php` para refletir
  o diretório base do site; não é necessário ajustar manualmente quando o
  site é movido entre `/vip` e `/`.

  **Importante:** o servidor embutido (`php -S`) não processa `.htaccess`, por
  isso `USE_PRETTY_URLS` é automaticamente definido como `false` nesse ambiente
  e as URLs voltam ao formato com query string. Em produção com Apache ou
  outro servidor com rewrite habilitado, as URLs amigáveis funcionam normalmente.

```json
[
  {
    "id": "balada_do_louco",
    "albumId": "insanity",
    "titulo": "Balada do Louco",
    "artistaOriginal": "Raul Seixas",
    "ano": 1974,
    "duracao": "5:30",
    "dificuldade": 3,
    "observacoes": "Arranjo para vozes mistas",
    "storagePath": "Insanity/Balada_do_louco",
    "image": "cover.jpg",
    "files": [
      {
        "label": "Partitura Sibelius",
        "relpath": "Balada_do_louco.sib",
        "type": "sib"
      }
    ]
  }
]
```

### cantores.json
Informações dos membros:
```json
[
  {
    "id": "ana_silva",
    "nome": "Ana Silva",
    "voz": "Soprano",
    "bioCurta": "Soprano com experiência...",
    "foto": null,
    "links": []
  }
]
```

### agenda.json
Eventos programados:
```json
[
  {
    "data": "2026-04-15",
    "hora": "20:00",
    "titulo": "Concerto Primavera",
    "local": "Teatro Municipal",
    "descricao": "Apresentação...",
    "link": null
  }
]
```

## Páginas Disponíveis

| URL | Descrição |
|-----|-----------|
| `index.php?p=home` | Home com hero e destaques |
| `index.php?p=arranjos` | Catálogo de arranjos com filtros |
| `index.php?p=arranjo&id=balada_do_louco` | Detalhe do arranjo com downloads |
| `index.php?p=historia` | Histórico do grupo |
| `index.php?p=cantores` | Lista de cantores |
| `index.php?p=cantor&id=ana_silva` | Perfil do cantor |
| `index.php?p=agenda` | Agenda de eventos |
| `index.php?p=contato` | Formulário de contato |

## Segurança

- ✅ **HTML Escape**: `htmlspecialchars()` em todas as saídas
- ✅ **ID Validation**: regex para slugs (`a-z0-9_-`)
- ✅ **Path Traversal Prevention**: bloqueio de `..` em caminhos
- ✅ **CSRF Token**: no formulário de contato
- ✅ **Honeypot**: campo oculto anti-bot
- ✅ **File Links**: gerados apenas a partir do JSON

## Funções Auxiliares (helpers.php)

```php
e($s)                                    // HTML escape
load_json($path)                         // Carregar JSON
valid_id($id)                            // Validar slug
asset($rel)                              // URL de assets
url($p, $params=[])                      // Montar querystring
render($view, $data=[])                  // Renderizar view
find_album($albums, $id)                 // Buscar álbum
find_arranjo($arranjos, $id)             // Buscar arranjo
find_cantor($cantores, $id)              // Buscar cantor
filter_arranjos($arranjos, $albumId, $q) // Filtrar arranjos
group_files_by_type($files)              // Agrupar por tipo
file_icon($type)                         // Ícone do arquivo
file_type_label($type)                   // Label do tipo
build_acervo_url($storagePath, $relpath) // Montar URL segura (arquivos agora são servidos de /vip/acervo/)
```

## Customização

### Cores
Edite `:root` em `assets/css/site.css`:
```css
:root {
    --primary-color: #667eea;
    --primary-dark: #764ba2;
    --secondary-color: #f093fb;
}
```

### Logo
Substitua `/assets/img/logo.png` por sua imagem e edite `nav.php`.

## Implantação

### Via FTP
1. Compacte a pasta `vip/`
2. Upload via FTP
3. Descompacte no servidor
4. Acesse via domínio

### Via Git
```bash
git clone seu-repositorio
cd vip
# Acessar via http://seu-dominio.com/vip
```

## Próximos Passos

- [ ] Adicionar mais arranjos ao `arranjos.json`
- [ ] Atualizar fotos dos cantores
- [ ] Customizar cores e logo
- [ ] Ativar `ENABLE_EMAIL` quando na hospedagem final
- [ ] Criar `.htaccess` para URLs amigáveis (opcional)
- [ ] Usar formulário de contato com realmente envio de email

## Suporte a Hospedag

Compatível com:
- Hospedagem Compartilhada (Hostinger, BlueHost, etc)
- VPS com PHP 8+
- Qualquer servidor com suporte a PHP

## Licença

Código livre para uso pessoal e comercial.

---

**VIP - Vox in Progress**  
Criado com ❤️ e PHP puro
