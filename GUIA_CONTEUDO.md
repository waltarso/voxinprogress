# VIP - Guia Rápido de Edição de Conteúdo

Este guia é para atualizar conteúdo sem mexer em código.

## 1) Músicas da Home (`homeOrder`)

Arquivo: `app/data/arranjos.json`

- Cada música pode ter `homeOrder`.
- Regras da Home:
  - só aparece quem tem `homeOrder` numérico
  - ordem crescente (`1, 2, 3...`)
  - sem `homeOrder` = não aparece

Exemplo:

```json
{
  "id": "under_pressure",
  "titulo": "Under Pressure",
  "homeOrder": 2
}
```

## 2) Cantores (`cantores.json`)

Arquivo: `app/data/cantores.json`

Campos principais:

- `id` (slug, ex: `joao_rangel`)
- `nome`
- `voz`
- `bioCurta`
- `foto` (opcional)
- `whatsapp` (opcional)
- `email` (opcional)
- `links` (Instagram/Facebook etc.)

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

## 3) Pasta de cada cantor (`equipe/<id>/`)

Estrutura recomendada:

- `equipe/<id>/<id>.md` (bio completa)
- imagem de perfil (`foto.jpg`, `foto.png`, `foto.webp`, `foto.avif`)

Exemplo:

- `equipe/walter_de_tarso/walter_de_tarso.md`
- `equipe/walter_de_tarso/foto.jpg`

## 4) Bio completa em Markdown

No arquivo `<id>.md`:

```md
# Nome do Cantor

## Sobre
Texto completo da biografia.

## Trajetória no VIP
- Entrada no grupo:
- Voz:
- Destaques:

## Links
- [Instagram](https://...)
- [Facebook](https://...)
```

## 5) Imagens de músicas e acervo

No `arranjos.json`:

- `storagePath` aponta para a pasta da música em `acervo/`
- `image` pode ser:
  - arquivo dentro do `acervo/<storagePath>/` (ex: `cover.jpg`)
  - caminho em `assets/` (ex: `img/minha_capa.jpg`)

## 6) Checklist rápido após editar

- Salvar os arquivos em UTF-8.
- Validar JSON (sem vírgula extra).
- Recarregar o navegador e testar:
  - Home
  - Repertório
  - Perfil de cantor
