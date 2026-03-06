# vip (Vox in Progress)

Site institucional do grupo vocal vip, feito em PHP puro + Bootstrap, com dados em JSON.

## Visão geral

- PHP 8+
- Sem framework
- Bootstrap 5 e Bootstrap Icons via CDN
- Dados em `app/data/*.json`
- Acervo de arquivos em `material/`
- Perfis dos colaboradores com foto e biografia em Markdown (`equipe/<id>/`)

## Estrutura principal

```
vip/
├── index.php
├── app/
│   ├── config.php
│   ├── helpers.php
│   ├── data/
│   │   ├── albums.json
│   │   ├── arranjos.json
│   │   ├── cantores.json
│   │   └── agenda.json
│   └── views/
│       ├── layout/
│       └── pages/
├── assets/
│   ├── css/site.css
│   └── img/
├── material/
└── equipe/
    └── <cantor_id>/
        ├── <cantor_id>.md
        └── foto.(jpg|png|webp|avif)
```

## Execução local

```bash
cd site/vip
php -S localhost:8000
```

Abra: `http://localhost:8000`

## Sincronizar novos arquivos do material

Quando voce adicionar novos arquivos/pastas em `material/`, rode:

```bash
php sync_material.php
```

O script:

- atualiza `app/data/arranjos.json` com os arquivos encontrados
- cria novos arranjos quando achar pastas novas
- gera backup automatico em `app/data/arranjos.json.bak-YYYYMMDD-HHMMSS`
- nao remove arranjos antigos automaticamente

## URLs

- Local (`php -S`): URLs com query string (`?p=...`)
- Produção Apache com rewrite: URLs amigáveis (ex.: `/arranjos/introspective`)

## Configuração (`app/config.php`)

- `SITE_NAME`, `SITE_TAGLINE`, `CONTACT_EMAIL`
- `ENABLE_EMAIL` (contato)
- `USE_PRETTY_URLS` é definido automaticamente:
  - `false` no `cli-server`
  - `true` fora dele

## Dados

### `arranjos.json`

Campos relevantes:

- `id`, `albumId`, `titulo`, `artistaOriginal`
- `storagePath`, `image`, `files[]`
- `homeOrder` (novo): controla exibição na Home

Regra da Home (Últimas Músicas):

- mostra apenas itens com `homeOrder` numérico
- ordena crescente (`1, 2, 3...`)
- não limita por quantidade (exibe todos com `homeOrder`)

### `cantores.json`

Campos relevantes:

- `id`, `nome`, `voz`, `bioCurta`, `foto`, `links[]`
- `entrada` (data de entrada no grupo)
- `saida` (`null` para integrantes atuais, data para colaboradores históricos)
- `whatsapp` (opcional)
- `email` (opcional)

Exemplo:

```json
{
  "id": "joao_rangel",
  "nome": "João Rangel",
  "voz": "Tenor",
  "bioCurta": "...",
  "foto": null,
  "whatsapp": "5511999999999",
  "email": "joao_rangel@exemplo.com",
  "links": [
    { "titulo": "Instagram", "url": "https://www.instagram.com/vox_in_progress/" },
    { "titulo": "Facebook", "url": "https://www.facebook.com/voxinprogress" }
  ]
}
```

## Perfis dos colaboradores (`equipe/<id>/`)

Cada colaborador pode ter pasta própria:

- `equipe/<id>/<id>.md` (bio completa)
- `equipe/<id>/bio.md` (fallback)
- foto local (auto-detect)

Prioridade de foto:

1. `foto` no JSON (URL externa, `assets/` ou arquivo relativo na pasta)
2. `foto.*`, `profile.*`, `perfil.*`, `avatar.*`
3. primeira imagem encontrada na pasta

## Terminologia da interface

- Navegação/listagem usa “Músicas” e “Repertório” (em vez de “Arranjos”)
- Rotas internas continuam `arranjos` / `arranjo` por compatibilidade

## Segurança implementada

- escape de HTML (`e()`)
- validação de IDs (`valid_id()`)
- proteção contra path traversal em arquivos de material
- CSRF + honeypot no formulário de contato
- leitura JSON tolerante a BOM UTF-8

## Observações

- Não depende de Composer/npm.
- Se mover o projeto entre raiz e subpasta, URLs de assets/material são ajustadas dinamicamente.

## Edição de conteúdo

Para atualização rápida de músicas, colaboradores e bios, veja [GUIA_CONTEUDO.md](GUIA_CONTEUDO.md).
